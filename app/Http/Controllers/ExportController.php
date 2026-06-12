<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\FeeStructure;
use App\Models\Payment;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    /**
     * Get the school record for PDF branding.
     */
    private function getSchool()
    {
        return School::first();
    }

    // ─── STUDENTS ────────────────────────────────────────────────────

    public function exportStudents(Request $request)
    {
        $query = Student::with('class');

        if ($request->filled('class_id')) {
            $query->where('school_class_id', $request->class_id);
        }

        if ($request->filled('status')) {
            $query->where('active', $request->status === 'active');
        }

        $students = $query->orderBy('first_name')->get();

        // Build filter label
        $filterParts = [];
        if ($request->filled('class_id')) {
            $cls = SchoolClass::find($request->class_id);
            if ($cls) {
                $filterParts[] = "Class: {$cls->name}";
            }
        }
        if ($request->filled('status')) {
            $filterParts[] = 'Status: '.ucfirst($request->status);
        }
        $filterLabel = count($filterParts) > 0 ? implode(' • ', $filterParts) : 'All Students';

        if ($request->query('format') === 'pdf') {
            $pdf = Pdf::loadView('exports.students', [
                'students' => $students,
                'school' => $this->getSchool(),
                'reportTitle' => 'Student Enrollment Report',
                'filterLabel' => $filterLabel,
            ])->setPaper('a4', 'landscape');

            return $pdf->download('students_'.now()->format('Y-m-d').'.pdf');
        }

        // CSV
        return $this->streamCsv('students_'.now()->format('Y-m-d').'.csv', function () use ($students) {
            echo "Student ID,First Name,Last Name,Class,Gender,Parent Name,Phone,DOB,Admission Date,Status\n";
            foreach ($students as $s) {
                echo implode(',', [
                    $s->student_id,
                    "\"{$s->first_name}\"",
                    "\"{$s->last_name}\"",
                    "\"{$s->class?->name}\"",
                    $s->gender,
                    "\"{$s->parent_name}\"",
                    $s->parent_phone,
                    $s->date_of_birth,
                    $s->admission_date,
                    $s->active ? 'Active' : 'Inactive',
                ])."\n";
            }
        });
    }

    // ─── PAYMENTS ────────────────────────────────────────────────────

    public function exportPayments(Request $request)
    {
        $query = Payment::with('student.class', 'term.academicYear', 'user');

        if ($request->filled('term_id')) {
            $query->where('term_id', $request->term_id);
        } elseif ($request->filled('year_id')) {
            $query->whereHas('term', fn ($q) => $q->where('academic_year_id', $request->year_id));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('student', function ($sq) use ($search) {
                    $sq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                })->orWhere('receipt_number', 'like', "%{$search}%");
            });
        }

        $payments = $query->latest('payment_date')->get();

        // Build filter label
        $filterParts = [];
        if ($request->filled('term_id')) {
            $term = Term::with('academicYear')->find($request->term_id);
            if ($term) {
                $filterParts[] = "Term: {$term->name} ({$term->academicYear->name})";
            }
        } elseif ($request->filled('year_id')) {
            $year = AcademicYear::find($request->year_id);
            if ($year) {
                $filterParts[] = "Year: {$year->name}";
            }
        }
        if ($request->filled('date_from')) {
            $filterParts[] = "From: {$request->date_from}";
        }
        if ($request->filled('date_to')) {
            $filterParts[] = "To: {$request->date_to}";
        }
        if ($request->filled('payment_type')) {
            $filterParts[] = 'Type: '.ucwords(str_replace('_', ' ', $request->payment_type));
        }
        if ($request->filled('payment_method')) {
            $filterParts[] = 'Method: '.strtoupper($request->payment_method);
        }
        if ($request->filled('search')) {
            $filterParts[] = "Search: '{$request->search}'";
        }

        $filterLabel = count($filterParts) > 0 ? implode(' • ', $filterParts) : 'All Payments';

        if ($request->query('format') === 'pdf') {
            $pdf = Pdf::loadView('exports.payments', [
                'payments' => $payments,
                'school' => $this->getSchool(),
                'reportTitle' => 'Payment History Report',
                'filterLabel' => $filterLabel,
            ])->setPaper('a4', 'landscape');

            return $pdf->download('payments_'.now()->format('Y-m-d').'.pdf');
        }

        // CSV
        return $this->streamCsv('payments_'.now()->format('Y-m-d').'.csv', function () use ($payments) {
            echo "Date,Receipt,Student Name,Student ID,Class,Fee Type,Method,Amount\n";
            foreach ($payments as $p) {
                echo implode(',', [
                    $p->payment_date,
                    $p->receipt_number,
                    "\"{$p->student->first_name} {$p->student->last_name}\"",
                    $p->student->student_id,
                    "\"{$p->student->class?->name}\"",
                    ucwords(str_replace('_', ' ', $p->payment_type)),
                    strtoupper($p->payment_method),
                    $p->amount,
                ])."\n";
            }
        });
    }

    // ─── EXPENSES ────────────────────────────────────────────────────

    public function exportExpenses(Request $request)
    {
        $query = Expense::with('category', 'user');

        if ($request->filled('category_id')) {
            $query->where('expense_category_id', $request->category_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('expense_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('expense_date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $expenses = $query->latest('expense_date')->get();

        // Build filter label
        $filterParts = [];
        if ($request->filled('category_id')) {
            $cat = ExpenseCategory::find($request->category_id);
            if ($cat) {
                $filterParts[] = "Category: {$cat->name}";
            }
        }
        if ($request->filled('date_from')) {
            $filterParts[] = "From: {$request->date_from}";
        }
        if ($request->filled('date_to')) {
            $filterParts[] = "To: {$request->date_to}";
        }
        if ($request->filled('search')) {
            $filterParts[] = "Search: '{$request->search}'";
        }
        $filterLabel = count($filterParts) > 0 ? implode(' • ', $filterParts) : 'All Expenses';

        if ($request->query('format') === 'pdf') {
            $pdf = Pdf::loadView('exports.expenses', [
                'expenses' => $expenses,
                'school' => $this->getSchool(),
                'reportTitle' => 'Expense Report',
                'filterLabel' => $filterLabel,
            ])->setPaper('a4', 'landscape');

            return $pdf->download('expenses_'.now()->format('Y-m-d').'.pdf');
        }

        // CSV
        return $this->streamCsv('expenses_'.now()->format('Y-m-d').'.csv', function () use ($expenses) {
            echo "Date,Title,Category,Amount,Payment Method,Description,Recorded By\n";
            foreach ($expenses as $e) {
                $desc = str_replace('"', '""', $e->description ?? '');
                echo implode(',', [
                    $e->expense_date,
                    "\"{$e->title}\"",
                    "\"{$e->category->name}\"",
                    $e->amount,
                    strtoupper($e->payment_method ?? 'N/A'),
                    "\"{$desc}\"",
                    "\"{$e->user->name}\"",
                ])."\n";
            }
        });
    }

    // ─── ANALYTICS ───────────────────────────────────────────────────

    public function exportAnalytics(Request $request)
    {
        $yearId = $request->query('year');
        $termId = $request->query('term');

        // Reuse the ReportController's analytics data method
        $reportController = app(ReportController::class);
        $analytics = $reportController->getAnalyticsData($yearId, $termId);

        // Filter label
        $filterParts = [];
        if ($termId) {
            $term = Term::with('academicYear')->find($termId);
            if ($term) {
                $filterParts[] = "Term: {$term->name} ({$term->academicYear->name})";
            }
        } elseif ($yearId) {
            $year = AcademicYear::find($yearId);
            if ($year) {
                $filterParts[] = "Year: {$year->name}";
            }
        }
        $filterLabel = count($filterParts) > 0 ? implode(' • ', $filterParts) : 'All Time';

        if ($request->query('format') === 'pdf') {
            $pdf = Pdf::loadView('exports.analytics', [
                'analytics' => $analytics,
                'school' => $this->getSchool(),
                'reportTitle' => 'Analytics Summary Report',
                'filterLabel' => $filterLabel,
            ])->setPaper('a4', 'landscape');

            return $pdf->download('analytics_'.now()->format('Y-m-d').'.pdf');
        }

        // CSV — flattened analytics
        return $this->streamCsv('analytics_'.now()->format('Y-m-d').'.csv', function () use ($analytics) {
            // Summary
            echo "=== SUMMARY ===\n";
            echo "Metric,Value\n";
            echo "Total Students,{$analytics['summary']['totalStudents']}\n";
            echo "Total Revenue,{$analytics['summary']['totalRevenue']}\n";
            echo "Total Expenses,{$analytics['summary']['totalExpenses']}\n";
            echo "Net Profit,{$analytics['summary']['netProfit']}\n";
            echo "Payment Rate,{$analytics['summary']['paymentRate']}%\n\n";

            // Revenue by term
            echo "=== REVENUE BY TERM ===\n";
            echo "Term,Revenue,Expected\n";
            foreach ($analytics['revenueByTerm'] as $item) {
                echo "\"{$item['term']}\",{$item['revenue']},{$item['expected']}\n";
            }
            echo "\n";

            // Expenses by category
            echo "=== EXPENSES BY CATEGORY ===\n";
            echo "Category,Amount,Percentage\n";
            foreach ($analytics['expensesByCategory'] as $item) {
                echo "\"{$item['category']}\",{$item['amount']},{$item['percentage']}%\n";
            }
            echo "\n";

            // Monthly trend
            echo "=== MONTHLY TREND ===\n";
            echo "Month,Revenue,Expenses\n";
            foreach ($analytics['monthlyTrend'] as $item) {
                echo "\"{$item['month']}\",{$item['revenue']},{$item['expenses']}\n";
            }
        });
    }

    // ─── STUDENT STATEMENT ───────────────────────────────────────────

    public function exportStudentStatement(Student $student)
    {
        $student->load('class', 'payments.term.academicYear');

        $payments = $student->payments()->with('term.academicYear')->latest('payment_date')->get();
        $totalPaid = $payments->sum('amount');

        // Calculate expected fees from fee structures
        $allYears = AcademicYear::latest()->get();
        $activeYear = $allYears->firstWhere(fn ($y) => $y->isActive());
        $feeBreakdown = collect();
        $totalExpected = 0;

        if ($activeYear && $student->class) {
            $classLevel = $student->class->level ?? null;
            $terms = Term::where('academic_year_id', $activeYear->id)->get();

            foreach ($terms as $term) {
                $fees = FeeStructure::where('term_id', $term->id)
                    ->when($classLevel, fn ($q) => $q->where('level', $classLevel))
                    ->get();

                foreach ($fees as $fee) {
                    $paidForType = $payments
                        ->where('term_id', $term->id)
                        ->where('payment_type', $fee->fee_type)
                        ->sum('amount');

                    $totalExpected += $fee->amount;

                    $feeBreakdown->push([
                        'fee_type' => ucwords(str_replace('_', ' ', $fee->fee_type)),
                        'term' => $term->name,
                        'expected' => $fee->amount,
                        'paid' => $paidForType,
                        'balance' => $fee->amount - $paidForType,
                    ]);
                }
            }
        }

        $totalBalance = $totalExpected - $totalPaid;

        $pdf = Pdf::loadView('exports.student-statement', [
            'student' => $student,
            'payments' => $payments,
            'feeBreakdown' => $feeBreakdown,
            'totalExpected' => $totalExpected,
            'totalPaid' => $totalPaid,
            'totalBalance' => $totalBalance,
            'school' => $this->getSchool(),
            'reportTitle' => 'Student Fee Statement',
        ]);

        $name = strtolower(str_replace(' ', '_', "{$student->first_name}_{$student->last_name}"));

        return $pdf->download("statement_{$name}_".now()->format('Y-m-d').'.pdf');
    }

    // ─── FEE COLLECTION ──────────────────────────────────────────────

    public function exportFeeCollection(Request $request)
    {
        $query = FeeStructure::with('term.academicYear');

        if ($request->filled('term_id')) {
            $query->where('term_id', $request->term_id);
        } elseif ($request->filled('year_id')) {
            $query->whereHas('term', fn ($q) => $q->where('academic_year_id', $request->year_id));
        }

        $feeStructures = $query->get();

        // Build filter label
        $filterParts = [];
        if ($request->filled('term_id')) {
            $term = Term::with('academicYear')->find($request->term_id);
            if ($term) {
                $filterParts[] = "Term: {$term->name} ({$term->academicYear->name})";
            }
        } elseif ($request->filled('year_id')) {
            $year = AcademicYear::find($request->year_id);
            if ($year) {
                $filterParts[] = "Year: {$year->name}";
            }
        }
        $filterLabel = count($filterParts) > 0 ? implode(' • ', $filterParts) : 'All Configured Fees';

        if ($request->query('format') === 'pdf') {
            $pdf = Pdf::loadView('exports.fee-collection', [
                'feeStructures' => $feeStructures,
                'school' => $this->getSchool(),
                'reportTitle' => 'Fee Collection Structure Report',
                'filterLabel' => $filterLabel,
            ])->setPaper('a4', 'landscape');

            return $pdf->download('fee_collection_'.now()->format('Y-m-d').'.pdf');
        }

        // CSV
        return $this->streamCsv('fee_collection_'.now()->format('Y-m-d').'.csv', function () use ($feeStructures) {
            echo "Term,Academic Year,Level,Fee Type,Amount\n";
            foreach ($feeStructures as $fs) {
                $level = ucfirst(str_replace('_', ' ', $fs->level));
                $feeType = ucwords(str_replace('_', ' ', $fs->fee_type));
                echo implode(',', [
                    "\"{$fs->term->name}\"",
                    "\"{$fs->term->academicYear->name}\"",
                    "\"{$level}\"",
                    "\"{$feeType}\"",
                    $fs->amount,
                ])."\n";
            }
        });
    }

    // ─── HELPERS ─────────────────────────────────────────────────────

    private function streamCsv(string $filename, callable $callback)
    {
        return response()->streamDownload(function () use ($callback) {
            // UTF-8 BOM for Excel compatibility
            echo "\xEF\xBB\xBF";
            $callback();
        }, $filename, [
            'Content-Type' => 'text/csv; charset=utf-8',
        ]);
    }
}
