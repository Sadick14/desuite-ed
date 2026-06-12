<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Term;
use App\Services\BalanceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FinanceController extends Controller
{
    public function collectionsIndex(Request $request)
    {
        $yearId = $request->year_id;
        $termId = $request->term_id;

        $years = AcademicYear::orderByDesc('ended_at')->orderByDesc('created_at')->get();
        $activeYear = $years->firstWhere(fn ($y) => $y->isActive());
        $selectedYear = $yearId ? AcademicYear::find($yearId) : $activeYear;
        $terms = $selectedYear?->terms ?? collect();

        $allStudents = collect();
        if ($selectedYear) {
            $allStudents = StudentEnrollment::where('academic_year_id', $selectedYear->id)
                ->with('student', 'student.class')
                ->get()
                ->pluck('student')
                ->unique('id');

            if ($termId) {
                $term = Term::find($termId);
                if ($term) {
                    $allStudents = $allStudents->map(function ($student) use ($term) {
                        $balance = BalanceService::forStudent($student, $term);
                        $student->current_balance = $balance['balance'];
                        $student->term_breakdown = $balance['breakdown'];

                        return $student;
                    });
                }
            } else {
                $allStudents = $allStudents->map(function ($student) use ($selectedYear) {
                    $yearBalance = BalanceService::outstandingBalanceForYear($student, $selectedYear);
                    $student->outstanding_balance = $yearBalance['total_balance'];
                    $student->has_outstanding = $yearBalance['has_outstanding'];
                    $student->term_breakdown = $yearBalance['term_breakdown'];

                    return $student;
                });
            }
        }

        $outstandingStudents = $allStudents->filter(fn ($s) => ($s->current_balance ?? $s->outstanding_balance ?? 0) > 0)
            ->sortByDesc(fn ($s) => $s->current_balance ?? $s->outstanding_balance ?? 0);

        return Inertia::render('Finance/Collections', [
            'years' => $years,
            'selectedYear' => $selectedYear,
            'terms' => $terms,
            'selectedTerm' => $termId ? Term::find($termId) : null,
            'outstandingStudents' => $outstandingStudents->values(),
            'totalOutstanding' => $outstandingStudents->sum(fn ($s) => $s->current_balance ?? $s->outstanding_balance ?? 0),
            'studentCount' => $allStudents->count(),
            'blockedStudentCount' => $allStudents->filter(fn ($s) => ($s->current_balance ?? $s->outstanding_balance ?? 0) > 0)->count(),
        ]);
    }

    public function paymentHistory(Request $request)
    {
        $yearId = $request->year_id;
        $studentId = $request->student_id;

        $years = AcademicYear::orderByDesc('ended_at')->orderByDesc('created_at')->get();
        $activeYear = $years->firstWhere(fn ($y) => $y->isActive());
        $selectedYear = $yearId ? AcademicYear::find($yearId) : $activeYear;

        $paymentsQuery = Payment::query();

        if ($selectedYear) {
            $termIds = $selectedYear->terms->pluck('id');
            $paymentsQuery->whereIn('term_id', $termIds);
        }

        if ($studentId) {
            $paymentsQuery->where('student_id', $studentId);
        }

        $payments = $paymentsQuery
            ->with('student', 'term')
            ->orderByDesc('created_at')
            ->paginate(50);

        return Inertia::render('Finance/PaymentHistory', [
            'payments' => $payments,
            'years' => $years,
            'selectedYear' => $selectedYear,
            'selectedStudent' => $studentId ? Student::find($studentId) : null,
            'totalAmount' => $paymentsQuery->sum('amount'),
        ]);
    }

    public function yearEndReport(Request $request)
    {
        $yearId = $request->year_id;

        $years = AcademicYear::orderByDesc('ended_at')->orderByDesc('created_at')->get();
        $activeYear = $years->firstWhere(fn ($y) => $y->isActive());
        $selectedYear = $yearId ? AcademicYear::find($yearId) : $activeYear;

        $yearData = null;
        if ($selectedYear) {
            $enrollments = StudentEnrollment::where('academic_year_id', $selectedYear->id)
                ->with('student', 'student.class')
                ->get();

            $students = $enrollments->pluck('student')->unique('id');

            $totalStudents = $students->count();
            $totalFeeStructure = 0;
            $totalCollected = 0;
            $totalOutstanding = 0;

            $studentBalances = $students->map(function ($student) use ($selectedYear, &$totalFeeStructure, &$totalCollected, &$totalOutstanding) {
                $yearBalance = BalanceService::outstandingBalanceForYear($student, $selectedYear);

                $totalFeeStructure += $yearBalance['term_breakdown']->sum(fn ($t) => $t['breakdown']->sum(fn ($b) => $b['expected']));
                $totalOutstanding += $yearBalance['total_balance'];

                return [
                    'student_id' => $student->id,
                    'name' => $student->full_name,
                    'class' => $student->class?->name,
                    'outstanding' => $yearBalance['total_balance'],
                    'status' => $yearBalance['has_outstanding'] ? 'Unpaid' : 'Settled',
                    'term_breakdown' => $yearBalance['term_breakdown'],
                ];
            })->sortByDesc('outstanding');

            $totalCollected = $totalFeeStructure - $totalOutstanding;

            $yearData = [
                'year_name' => $selectedYear->name,
                'total_students' => $totalStudents,
                'total_fee_structure' => $totalFeeStructure,
                'total_collected' => $totalCollected,
                'total_outstanding' => $totalOutstanding,
                'collection_rate' => $totalFeeStructure > 0 ? round(($totalCollected / $totalFeeStructure) * 100, 2) : 0,
                'unpaid_students' => $studentBalances->filter(fn ($s) => $s['outstanding'] > 0)->count(),
                'student_details' => $studentBalances,
            ];
        }

        return Inertia::render('Finance/YearEndReport', [
            'years' => $years,
            'selectedYear' => $selectedYear,
            'yearData' => $yearData,
        ]);
    }

    public function studentFinancial($studentId)
    {
        $student = Student::findOrFail($studentId);

        $enrollments = $student->enrollments()
            ->with('academicYear', 'academicYear.terms')
            ->orderByDesc('academic_year_id')
            ->get();

        $financialHistory = $enrollments->map(function ($enrollment) use ($student) {
            $year = $enrollment->academicYear;
            $yearBalance = BalanceService::outstandingBalanceForYear($student, $year);

            return [
                'year_name' => $year->name,
                'year_status' => $year->isEnded() ? 'Ended' : 'Active',
                'total_outstanding' => $yearBalance['total_balance'],
                'has_outstanding' => $yearBalance['has_outstanding'],
                'term_breakdown' => $yearBalance['term_breakdown'],
            ];
        });

        return Inertia::render('Finance/StudentFinancial', [
            'student' => $student,
            'financialHistory' => $financialHistory,
            'currentBalance' => $student->getCurrentBalance(),
        ]);
    }
}
