<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $academicYears = AcademicYear::with('terms')->orderByDesc('is_active')->get();
        $classes = SchoolClass::all();

        $selectedClassId = $request->class_id;
        $selectedTermId = $request->term_id;

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
            'classes' => $classes,
            'students' => $students,
            'selectedClassId' => $selectedClassId,
            'selectedTermId' => $selectedTermId,
        ]);
    }

    public function show(Student $student, Request $request)
    {
        $termId = $request->term_id;
        $academicYearId = $request->academic_year_id;

        $terms = Term::with('academicYear')->get();
        $academicYears = AcademicYear::with('terms')->get();

        // Get all grades for this student in selected term/year
        $gradesQuery = Grade::with(['exam.course', 'exam.term'])
            ->where('student_id', $student->id);

        if ($termId) {
            $gradesQuery->whereHas('exam', fn ($q) => $q->where('term_id', $termId));
        }

        if ($academicYearId) {
            $gradesQuery->whereHas('exam', fn ($q) => $q->where('academic_year_id', $academicYearId));
        }

        $grades = $gradesQuery->get();

        // Calculate summary stats
        $totalScore = 0;
        $maxScore = 0;
        $passedCount = 0;

        foreach ($grades as $grade) {
            $totalScore += $grade->score;
            $maxScore += $grade->exam->max_score;
            if ($grade->score >= $grade->exam->pass_score) {
                $passedCount++;
            }
        }

        $averageScore = $grades->count() > 0 ? round($totalScore / $grades->count(), 2) : 0;
        $percentage = $maxScore > 0 ? round(($totalScore / $maxScore) * 100, 2) : 0;

        return Inertia::render('Reports/Show', [
            'student' => $student,
            'terms' => $terms,
            'academicYears' => $academicYears,
            'grades' => $grades,
            'selectedTermId' => $termId,
            'selectedAcademicYearId' => $academicYearId,
            'summary' => [
                'totalScore' => $totalScore,
                'maxScore' => $maxScore,
                'averageScore' => $averageScore,
                'percentage' => $percentage,
                'totalSubjects' => $grades->count(),
                'passedSubjects' => $passedCount,
                'failedSubjects' => $grades->count() - $passedCount,
            ],
        ]);
    }

    public function download(Student $student, Request $request)
    {
        $termId = $request->term_id;
        $academicYearId = $request->academic_year_id;

        $term = $termId ? Term::find($termId) : null;
        $academicYear = $academicYearId ? AcademicYear::find($academicYearId) : null;

        // Get grades
        $gradesQuery = Grade::with(['exam.course', 'exam.term'])
            ->where('student_id', $student->id);

        if ($termId) {
            $gradesQuery->whereHas('exam', fn ($q) => $q->where('term_id', $termId));
        }

        if ($academicYearId) {
            $gradesQuery->whereHas('exam', fn ($q) => $q->where('academic_year_id', $academicYearId));
        }

        $grades = $gradesQuery->get();

        // Calculate summary
        $totalScore = 0;
        $maxScore = 0;
        $passedCount = 0;

        foreach ($grades as $grade) {
            $totalScore += $grade->score;
            $maxScore += $grade->exam->max_score;
            if ($grade->score >= $grade->exam->pass_score) {
                $passedCount++;
            }
        }

        $averageScore = $grades->count() > 0 ? round($totalScore / $grades->count(), 2) : 0;
        $percentage = $maxScore > 0 ? round(($totalScore / $maxScore) * 100, 2) : 0;

        $pdf = Pdf::loadView('reports.report-card', [
            'student' => $student,
            'term' => $term,
            'academicYear' => $academicYear,
            'grades' => $grades,
            'summary' => [
                'totalScore' => $totalScore,
                'maxScore' => $maxScore,
                'averageScore' => $averageScore,
                'percentage' => $percentage,
                'totalSubjects' => $grades->count(),
                'passedSubjects' => $passedCount,
                'failedSubjects' => $grades->count() - $passedCount,
            ],
        ]);

        return $pdf->download("{$student->first_name}_{$student->last_name}_report_card.pdf");
    }
}
