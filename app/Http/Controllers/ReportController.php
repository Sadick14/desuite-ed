<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\FeeStructure;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\StudentEnrollment;
use App\Models\Term;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        $terms = Term::with('academicYear')->get();
        $academicYears = AcademicYear::all();
        $expenseCategories = ExpenseCategory::all();
        $classes = SchoolClass::all();

        return Inertia::render('Reports/Index', [
            'terms' => $terms,
            'academicYears' => $academicYears,
            'expenseCategories' => $expenseCategories,
            'classes' => $classes,
        ]);
    }

    public function analytics(Request $request)
    {
        $yearId = $request->query('year');
        $termId = $request->query('term');

        $analytics = $this->getAnalyticsData($yearId, $termId);
        $terms = Term::with('academicYear')->get();
        $academicYears = AcademicYear::all();

        return Inertia::render('Reports/Analytics', [
            'analytics' => $analytics,
            'terms' => $terms,
            'academicYears' => $academicYears,
            'filters' => [
                'year' => $yearId ? (int) $yearId : null,
                'term' => $termId ? (int) $termId : null,
            ],
        ]);
    }

    public function getAnalyticsData($yearId = null, $termId = null)
    {
        // 1. Filter Students
        $studentQuery = StudentEnrollment::query();
        if ($yearId) {
            $studentQuery->where('academic_year_id', $yearId);
        } else {
            $activeYear = AcademicYear::where('is_active', true)->first();
            if ($activeYear) {
                $studentQuery->where('academic_year_id', $activeYear->id);
            }
        }
        $totalStudents = $studentQuery->distinct('student_id')->count('student_id');

        // 2. Filter Revenue
        $revenueQuery = Payment::query();
        if ($termId) {
            $revenueQuery->where('term_id', $termId);
        } elseif ($yearId) {
            $revenueQuery->whereHas('term', fn ($q) => $q->where('academic_year_id', $yearId));
        }
        $totalRevenue = $revenueQuery->sum('amount');

        // 3. Filter Expenses
        $expenseQuery = Expense::query();
        if ($termId) {
            $term = Term::find($termId);
            if ($term) {
                $expenseQuery->whereBetween('expense_date', [$term->start_date, $term->end_date]);
            }
        } elseif ($yearId) {
            $year = AcademicYear::find($yearId);
            if ($year) {
                $expenseQuery->whereBetween('expense_date', [$year->start_date, $year->end_date]);
            }
        }
        $totalExpenses = $expenseQuery->sum('amount');

        $netProfit = $totalRevenue - $totalExpenses;

        // 4. Expected Revenue
        $expectedQuery = FeeStructure::query();
        if ($termId) {
            $expectedQuery->where('term_id', $termId);
        } elseif ($yearId) {
            $expectedQuery->whereHas('term', fn ($q) => $q->where('academic_year_id', $yearId));
        }
        $expectedRevenue = $expectedQuery->sum('amount');
        $paymentRate = $expectedRevenue > 0 ? round(($totalRevenue / $expectedRevenue) * 100) : 0;
        $paymentRate = min(100, max(0, $paymentRate));

        // 5. Revenue by Term
        $termQuery = Term::with('payments');
        if ($yearId) {
            $termQuery->where('academic_year_id', $yearId);
        }
        $revenueByTerm = $termQuery->get()->map(function ($term) {
            $revenue = $term->payments->sum('amount');
            $expected = FeeStructure::where('term_id', $term->id)->sum('amount');

            return [
                'term' => $term->name,
                'revenue' => (int) $revenue,
                'expected' => (int) $expected,
            ];
        });

        // 6. Expenses by Category
        $expensesByCategory = ExpenseCategory::query()->get()->map(function ($category) use ($yearId, $termId, $totalExpenses) {
            $query = Expense::where('expense_category_id', $category->id);
            if ($termId) {
                $term = Term::find($termId);
                if ($term) {
                    $query->whereBetween('expense_date', [$term->start_date, $term->end_date]);
                }
            } elseif ($yearId) {
                $year = AcademicYear::find($yearId);
                if ($year) {
                    $query->whereBetween('expense_date', [$year->start_date, $year->end_date]);
                }
            }
            $amount = $query->sum('amount');
            $percentage = $totalExpenses > 0 ? round(($amount / $totalExpenses) * 100) : 0;

            return [
                'category' => $category->name,
                'amount' => (int) $amount,
                'percentage' => $percentage,
            ];
        })->filter(fn ($cat) => $cat['amount'] > 0)->values();

        // 7. Students by Level
        $classQuery = SchoolClass::query();
        if ($yearId) {
            $classQuery->where('academic_year_id', $yearId);
        }
        $studentsByLevel = $classQuery->withCount(['enrollments' => function ($q) {
            $q->where('status', 'active');
        }])->get()->map(function ($class) {
            return [
                'level' => $class->levelLabel,
                'count' => $class->enrollments_count,
            ];
        })->filter(fn ($level) => $level['count'] > 0)
            ->groupBy('level')
            ->map(fn ($group, $level) => [
                'level' => $level,
                'count' => $group->sum('count'),
            ])->values();

        // 8. Monthly Trend (last 6 months)
        $monthlyTrend = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);

            $revQ = Payment::whereYear('payment_date', $date->year)
                ->whereMonth('payment_date', $date->month);
            $expQ = Expense::whereYear('expense_date', $date->year)
                ->whereMonth('expense_date', $date->month);

            if ($termId) {
                $revQ->where('term_id', $termId);
                $term = Term::find($termId);
                if ($term) {
                    $expQ->whereBetween('expense_date', [$term->start_date, $term->end_date]);
                }
            } elseif ($yearId) {
                $revQ->whereHas('term', fn ($q) => $q->where('academic_year_id', $yearId));
                $year = AcademicYear::find($yearId);
                if ($year) {
                    $expQ->whereBetween('expense_date', [$year->start_date, $year->end_date]);
                }
            }

            $revenue = $revQ->sum('amount');
            $expenses = $expQ->sum('amount');

            $monthlyTrend->push([
                'month' => $date->format('M Y'),
                'revenue' => (int) $revenue,
                'expenses' => (int) $expenses,
            ]);
        }

        return [
            'summary' => [
                'totalStudents' => $totalStudents,
                'totalRevenue' => (int) $totalRevenue,
                'totalExpenses' => (int) $totalExpenses,
                'netProfit' => (int) $netProfit,
                'paymentRate' => $paymentRate,
            ],
            'revenueByTerm' => $revenueByTerm->values(),
            'expensesByCategory' => $expensesByCategory,
            'studentsByLevel' => $studentsByLevel,
            'monthlyTrend' => $monthlyTrend,
        ];
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            'report_type' => ['required', 'in:financial_summary,student_enrollment,fee_collection,expense_report,payment_history'],
            'format' => ['required', 'in:pdf,excel,csv'],
            'filters' => ['nullable', 'array'],
        ]);

        $reportType = $data['report_type'];
        $format = $data['format'] === 'excel' ? 'csv' : $data['format'];
        $filters = $data['filters'] ?? [];

        // Build a request object with parameters matching what ExportController expects
        $exportParams = array_merge($filters, ['format' => $format]);

        // Map report-specific filter key names if they differ
        if ($reportType === 'expense_report') {
            if (isset($filters['start_date'])) {
                $exportParams['date_from'] = $filters['start_date'];
            }
            if (isset($filters['end_date'])) {
                $exportParams['date_to'] = $filters['end_date'];
            }
        }
        if ($reportType === 'financial_summary') {
            if (isset($filters['academic_year_id'])) {
                $exportParams['year_id'] = $filters['academic_year_id'];
            }
        }

        $exportRequest = new Request($exportParams);
        $exportController = app(ExportController::class);

        return match ($reportType) {
            'student_enrollment' => $exportController->exportStudents($exportRequest),
            'expense_report' => $exportController->exportExpenses($exportRequest),
            'fee_collection' => $exportController->exportFeeCollection($exportRequest),
            'payment_history', 'financial_summary' => $exportController->exportPayments($exportRequest),
            default => response()->json(['error' => 'Invalid report type'], 400),
        };
    }
}
