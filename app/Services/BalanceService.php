<?php

namespace App\Services;

use App\Models\FeeStructure;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Support\Facades\DB;

class BalanceService
{
    /**
     * Get financial breakdown for a student in a given term
     */
    public static function forStudent(Student $student, Term $term): array
    {
        $level = $student->class?->level;

        $expected = $level
            ? FeeStructure::where('term_id', $term->id)
                ->where('level', $level)
                ->get()
                ->groupBy('fee_type')
                ->map(fn ($fees) => $fees->sum('amount'))
                ->toArray()
            : [];

        $paid = Payment::where('student_id', $student->id)
            ->where('term_id', $term->id)
            ->get()
            ->groupBy('payment_type')
            ->map(fn ($payments) => $payments->sum('amount'))
            ->toArray();

        $breakdown = [];
        $totalExpected = 0;
        $totalPaid = 0;

        foreach (array_keys($expected + $paid) as $feeType) {
            $feeExpected = $expected[$feeType] ?? 0;
            $feePaid = $paid[$feeType] ?? 0;
            $feeBalance = max(0, $feeExpected - $feePaid);

            $breakdown[] = [
                'fee_type' => $feeType,
                'expected' => $feeExpected,
                'paid' => $feePaid,
                'balance' => $feeBalance,
            ];

            $totalExpected += $feeExpected;
            $totalPaid += $feePaid;
        }

        return [
            'expected' => $totalExpected,
            'paid' => $totalPaid,
            'balance' => max(0, $totalExpected - $totalPaid),
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Get balance for a specific fee type
     */
    public static function forStudentAndFeeType(Student $student, Term $term, string $feeType): array
    {
        $level = $student->class?->level;

        $expected = $level
            ? FeeStructure::where('term_id', $term->id)
                ->where('level', $level)
                ->where('fee_type', $feeType)
                ->sum('amount')
            : 0;

        $paid = Payment::where('student_id', $student->id)
            ->where('term_id', $term->id)
            ->where('payment_type', $feeType)
            ->sum('amount');

        return [
            'expected' => $expected,
            'paid' => $paid,
            'balance' => max(0, $expected - $paid),
        ];
    }

    /**
     * Check if a payment would exceed the balance (for non-flexible fees)
     */
    public static function canPay(Student $student, Term $term, string $feeType, float $amount): bool
    {
        if ($feeType === 'feeding_fees') {
            return true;
        }

        $balance = self::forStudentAndFeeType($student, $term, $feeType);

        return $amount <= $balance['balance'] + 0.01;
    }

    /**
     * Eagerly calculate and attach balances for all students
     */
    public static function attachBalances($students, Term $term)
    {
        // Get all fee structures for this term grouped by level
        $feeStructures = FeeStructure::where('term_id', $term->id)
            ->get()
            ->groupBy('level');

        // Get all payments for this term grouped by student_id and payment_type
        $payments = Payment::where('term_id', $term->id)
            ->select('student_id', 'payment_type', DB::raw('SUM(amount) as total_paid'))
            ->groupBy('student_id', 'payment_type')
            ->get()
            ->groupBy('student_id');

        foreach ($students as $student) {
            $level = $student->class?->level;
            $studentFees = $level ? ($feeStructures[$level] ?? collect()) : collect();
            $studentPayments = $payments[$student->id] ?? collect();

            $expected = $studentFees->groupBy('fee_type')
                ->map(fn ($fees) => $fees->sum('amount'))
                ->toArray();

            $paid = $studentPayments->pluck('total_paid', 'payment_type')
                ->map(fn ($amount) => (float) $amount)
                ->toArray();

            $breakdown = [];
            $totalExpected = 0;
            $totalPaid = 0;

            foreach (array_keys($expected + $paid) as $feeType) {
                $feeExpected = $expected[$feeType] ?? 0;
                $feePaid = $paid[$feeType] ?? 0;
                $feeBalance = max(0, $feeExpected - $feePaid);

                $breakdown[] = [
                    'fee_type' => $feeType,
                    'expected' => $feeExpected,
                    'paid' => $feePaid,
                    'balance' => $feeBalance,
                ];

                $totalExpected += $feeExpected;
                $totalPaid += $feePaid;
            }

            $student->balances = [
                'expected' => $totalExpected,
                'paid' => $totalPaid,
                'balance' => max(0, $totalExpected - $totalPaid),
                'breakdown' => $breakdown,
            ];
        }

        return $students;
    }
}
