<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\StudentFeedingBalance;
use App\Models\Term;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FeedingFeeService
{
    /**
     * Calculate weekly feeding fee for a student based on attendance
     */
    public static function calculateWeeklyFee(Student $student, Term $term, int $weekNumber): array
    {
        // Get daily feeding fee rate
        $feeStructure = FeeStructure::where('term_id', $term->id)
            ->where('level', $student->class?->level)
            ->where('fee_type', 'feeding_fees')
            ->where('is_recurring', true)
            ->first();

        if (! $feeStructure || ! $feeStructure->daily_rate) {
            return [
                'days_attended' => 0,
                'amount_owed' => 0,
                'daily_rate' => 0,
            ];
        }

        // Calculate week date range (Monday to Friday)
        $weekStartDate = $term->start_date->copy()
            ->addWeeks($weekNumber - 1)
            ->startOfWeek(Carbon::MONDAY);
        $weekEndDate = $weekStartDate->copy()->addDays(4)->endOfDay(); // Friday

        // Count attendance days for this student in this week
        $daysAttended = Attendance::where('student_id', $student->id)
            ->whereBetween('attendance_date', [$weekStartDate, $weekEndDate])
            ->where('status', 'present')
            ->count();

        // Ensure max 5 days (Mon-Fri)
        $daysAttended = min($daysAttended, 5);

        // Calculate amount owed
        $amountOwed = $daysAttended * $feeStructure->daily_rate;

        return [
            'days_attended' => $daysAttended,
            'amount_owed' => $amountOwed,
            'daily_rate' => $feeStructure->daily_rate,
            'week_start' => $weekStartDate,
            'week_end' => $weekEndDate,
        ];
    }

    /**
     * Reconcile feeding fee for a week (auto-calculate and create balance record)
     */
    public static function reconcileWeeklyFee(Student $student, Term $term, int $weekNumber): StudentFeedingBalance
    {
        // Get previous week's carryforward
        $previousWeek = StudentFeedingBalance::where('student_id', $student->id)
            ->where('term_id', $term->id)
            ->where('week_number', $weekNumber - 1)
            ->first();

        $carryforwardBalance = $previousWeek?->carryforward_to_next_week ?? 0;

        // Calculate this week's obligation
        $weekData = self::calculateWeeklyFee($student, $term, $weekNumber);

        // Get or create balance record for this week
        $balance = StudentFeedingBalance::updateOrCreate(
            [
                'student_id' => $student->id,
                'term_id' => $term->id,
                'week_number' => $weekNumber,
            ],
            [
                'carryforward_balance' => $carryforwardBalance,
                'days_attended' => $weekData['days_attended'],
                'amount_owed' => $weekData['amount_owed'],
                'amount_paid' => 0, // Will be updated when payment is recorded
            ]
        );

        return $balance;
    }

    /**
     * Get current week number for a term
     */
    public static function getCurrentWeekNumber(Term $term): int
    {
        if (! $term->start_date || ! $term->end_date) {
            return 1;
        }

        $now = Carbon::now();
        if ($now->isBefore($term->start_date)) {
            return 0;
        }

        $weeksElapsed = $term->start_date->diffInWeeks($now);

        return max(1, $weeksElapsed + 1);
    }

    /**
     * Get feeding fee balance for a student in current term
     */
    public static function getStudentFeedingBalance(Student $student, Term $term): array
    {
        $balances = StudentFeedingBalance::where('student_id', $student->id)
            ->where('term_id', $term->id)
            ->orderBy('week_number')
            ->get();

        if ($balances->isEmpty()) {
            return [
                'total_owed' => 0,
                'total_paid' => 0,
                'carryforward' => 0,
                'outstanding' => 0,
                'weekly_breakdown' => [],
            ];
        }

        $totalOwed = 0;
        $totalPaid = 0;
        $latestOutstanding = 0;

        $breakdown = $balances->map(function ($balance) use (&$totalOwed, &$totalPaid, &$latestOutstanding) {
            $totalOwed += $balance->amount_owed;
            $totalPaid += $balance->amount_paid;
            $latestOutstanding = $balance->outstanding_balance;

            return [
                'week' => $balance->week_number,
                'days_attended' => $balance->days_attended,
                'amount_owed' => (float) $balance->amount_owed,
                'amount_paid' => (float) $balance->amount_paid,
                'carryforward' => (float) $balance->carryforward_balance,
                'outstanding' => (float) $balance->outstanding_balance,
            ];
        })->toArray();

        return [
            'total_owed' => $totalOwed,
            'total_paid' => $totalPaid,
            'carryforward' => $latestOutstanding > 0 ? $latestOutstanding : 0,
            'outstanding' => $latestOutstanding > 0 ? $latestOutstanding : 0,
            'weekly_breakdown' => $breakdown,
        ];
    }

    /**
     * Record payment against feeding fees with carryforward handling
     */
    public static function recordFeedingFeePayment(Student $student, Term $term, float $amount): array
    {
        $remaining = $amount;
        $allocations = [];

        // Get all unsettled weeks (ordered by week number)
        $weeks = StudentFeedingBalance::where('student_id', $student->id)
            ->where('term_id', $term->id)
            ->where(DB::raw('carryforward_balance + amount_owed - amount_paid'), '>', 0)
            ->orderBy('week_number')
            ->get();

        foreach ($weeks as $week) {
            if ($remaining <= 0) {
                break;
            }

            $outstanding = $week->outstanding_balance;
            $allocated = min($remaining, $outstanding);

            $week->increment('amount_paid', $allocated);

            $allocations[] = [
                'week' => $week->week_number,
                'allocated' => $allocated,
            ];

            $remaining -= $allocated;
        }

        return [
            'total_allocated' => $amount - $remaining,
            'remaining' => $remaining,
            'allocations' => $allocations,
        ];
    }
}
