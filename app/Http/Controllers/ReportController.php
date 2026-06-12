<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentMark;
use App\Models\Term;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $academicYears = AcademicYear::with('terms')->latest()->get();
        $activeYear = $academicYears->firstWhere(fn ($year) => $year->isActive()) ?? $academicYears->first();
        $currentTerm = Term::where('is_active', true)->first();

        // Get selected year and term
        $selectedYearId = $request->year_id ?? $activeYear?->id;
        $selectedTermId = $request->term_id ?? $currentTerm?->id;

        $selectedYear = $selectedYearId ? AcademicYear::find($selectedYearId) : null;
        $selectedTerm = $selectedTermId ? Term::find($selectedTermId) : null;

        // Load all classes
        $classes = SchoolClass::with([
            'students' => fn ($q) => $q->where('active', true)->orderBy('last_name')->orderBy('first_name'),
        ])->get();

        // Load marks for each student in this class for the selected term
        if ($selectedTerm) {
            $classes->each(function ($class) use ($selectedTerm) {
                $class->students->each(function ($student) use ($selectedTerm) {
                    $student->marks = StudentMark::where('student_id', $student->id)
                        ->where('term_id', $selectedTerm->id)
                        ->with(['course', 'assessmentSetting.gradingScale'])
                        ->orderBy('course_id')
                        ->get();
                });
            });
        }

        $selectedClassId = $request->class_id;

        $students = collect();
        if ($selectedClassId) {
            $students = Student::where('school_class_id', $selectedClassId)
                ->where('active', true)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();
        }

        return Inertia::render('Reports/Index', [
            'academicYears' => $academicYears,
            'activeYear' => $activeYear,
            'currentTerm' => $currentTerm,
            'classes' => $classes,
            'students' => $students,
            'selectedYearId' => $selectedYearId,
            'selectedTermId' => $selectedTermId,
            'selectedClassId' => $selectedClassId,
        ]);
    }

    public function show(Student $student, Request $request)
    {
        $termId = $request->term_id;
        $academicYearId = $request->academic_year_id;

        $terms = Term::orderBy('name')->get();
        $academicYears = AcademicYear::with('terms')->latest()->get();

        // Get all marks for this student in selected term
        $marksQuery = StudentMark::with(['course', 'term', 'assessmentSetting.gradingScale'])
            ->where('student_id', $student->id);

        if ($termId) {
            $marksQuery->where('term_id', $termId);
        }

        $marks = $marksQuery->orderBy('course_id')->get();

        // Calculate summary stats
        $totalFinalScore = 0;
        $passedCount = 0;
        $totalCount = $marks->count();

        foreach ($marks as $mark) {
            if ($mark->final_score) {
                $totalFinalScore += $mark->final_score;
                // Passing grade is typically 50 or higher
                if ($mark->final_score >= 50) {
                    $passedCount++;
                }
            }
        }

        $averageScore = $totalCount > 0 ? round($totalFinalScore / $totalCount, 2) : 0;

        return Inertia::render('Reports/Show', [
            'student' => $student,
            'terms' => $terms,
            'academicYears' => $academicYears,
            'grades' => $marks,
            'selectedTermId' => $termId,
            'selectedAcademicYearId' => $academicYearId,
            'summary' => [
                'totalScore' => round($totalFinalScore, 2),
                'maxScore' => 100 * $totalCount,
                'averageScore' => $averageScore,
                'percentage' => $averageScore,
                'totalSubjects' => $totalCount,
                'passedSubjects' => $passedCount,
                'failedSubjects' => $totalCount - $passedCount,
            ],
        ]);
    }

    public function download(Student $student, Request $request)
    {
        $termId = $request->term_id;

        $term = $termId ? Term::find($termId) : null;

        // Get marks
        $marksQuery = StudentMark::with(['course', 'term', 'assessmentSetting.gradingScale'])
            ->where('student_id', $student->id);

        if ($termId) {
            $marksQuery->where('term_id', $termId);
        }

        $marks = $marksQuery->orderBy('course_id')->get();

        // Calculate summary
        $totalFinalScore = 0;
        $passedCount = 0;
        $totalCount = $marks->count();

        foreach ($marks as $mark) {
            if ($mark->final_score) {
                $totalFinalScore += $mark->final_score;
                if ($mark->final_score >= 50) {
                    $passedCount++;
                }
            }
        }

        $averageScore = $totalCount > 0 ? round($totalFinalScore / $totalCount, 2) : 0;

        // For now, just redirect to view with download message
        // PDF generation can be enhanced later
        return response()->json([
            'message' => 'PDF export coming soon. Please use the view page for now.',
        ], 501);
    }

    public function analytics(Request $request)
    {
        $selectedYearId = $request->year_id;

        $allYears = AcademicYear::with('terms')->orderByDesc('ended_at')->orderByDesc('created_at')->get();
        $activeYear = $allYears->firstWhere(fn ($y) => ! $y->isEnded());
        $selectedYear = $selectedYearId ? $allYears->find($selectedYearId) : $activeYear;

        // Get statistics for selected year
        $yearStats = null;
        $yearAnalytics = null;

        if ($selectedYear) {
            $yearStats = [
                'name' => $selectedYear->name,
                'is_ended' => $selectedYear->isEnded(),
                'total_students' => StudentEnrollment::whereHas('academicYear', fn ($q) => $q->where('id', $selectedYear->id))
                    ->distinct('student_id')
                    ->count('student_id'),
                'total_marks_recorded' => StudentMark::whereHas('term', fn ($q) => $q->where('academic_year_id', $selectedYear->id))
                    ->count(),
                'average_gpa' => StudentMark::whereHas('term', fn ($q) => $q->where('academic_year_id', $selectedYear->id))
                    ->avg('final_score'),
            ];

            // Detailed analytics
            $marks = StudentMark::whereHas('term', fn ($q) => $q->where('academic_year_id', $selectedYear->id))->get();

            $gradeDistribution = [
                'A' => 0,
                'B' => 0,
                'C' => 0,
                'D' => 0,
                'E' => 0,
                'F' => 0,
            ];

            foreach ($marks as $mark) {
                if ($mark->letter_grade) {
                    $gradeDistribution[$mark->letter_grade] = ($gradeDistribution[$mark->letter_grade] ?? 0) + 1;
                }
            }

            $passedCount = $marks->filter(fn ($m) => $m->final_score >= 50)->count();
            $failedCount = $marks->count() - $passedCount;

            // Class performance
            $classBreakdown = StudentEnrollment::whereHas('academicYear', fn ($q) => $q->where('id', $selectedYear->id))
                ->with('class')
                ->get()
                ->groupBy('school_class_id')
                ->map(function ($enrollments) use ($marks) {
                    $classId = $enrollments->first()->school_class_id;
                    $studentIds = $enrollments->pluck('student_id')->toArray();

                    $classMarks = $marks->filter(fn ($m) => in_array($m->student_id, $studentIds));
                    $passed = $classMarks->filter(fn ($m) => $m->final_score >= 50)->count();
                    $total = $classMarks->count();

                    return [
                        'class_name' => $enrollments->first()->class->name,
                        'student_count' => $enrollments->count(),
                        'avg_score' => $classMarks->avg('final_score'),
                        'pass_rate' => $total > 0 ? ($passed / $total) * 100 : 0,
                    ];
                })
                ->values()
                ->toArray();

            $yearAnalytics = [
                'total_students' => $yearStats['total_students'],
                'total_marks_recorded' => $yearStats['total_marks_recorded'],
                'average_gpa' => $yearStats['average_gpa'],
                'students_passed' => $passedCount,
                'students_failed' => $failedCount,
                'gradeDistribution' => $gradeDistribution,
                'classBreakdown' => $classBreakdown,
            ];
        }

        return Inertia::render('Analytics/Index', [
            'academicYears' => $allYears,
            'activeYear' => $activeYear,
            'selectedYear' => $selectedYear,
            'yearStats' => $yearStats,
            'yearAnalytics' => $yearAnalytics,
        ]);
    }
}
