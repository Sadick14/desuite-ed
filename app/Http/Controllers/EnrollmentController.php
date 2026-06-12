<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\StudentEnrollment;
use App\Models\Term;
use Inertia\Inertia;

class EnrollmentController extends Controller
{
    public function index()
    {
        $academicYears = AcademicYear::with(['enrollments.student', 'enrollments.class', 'classes'])->latest()->get()->map(function ($year) {
            return [
                'id' => $year->id,
                'name' => $year->name,
                'is_active' => $year->isActive(),
                'classes' => $year->classes->map(fn ($cls) => [
                    'id' => $cls->id,
                    'name' => $cls->name,
                    'level' => $cls->level,
                    'academic_year_id' => $cls->academic_year_id,
                    'enrollments_count' => $cls->enrollments->count(),
                ])->toArray(),
            ];
        })->sortByDesc('is_active')->values();

        $currentYear = $academicYears->firstWhere('is_active', true) ?? $academicYears->first();
        $activeTerm = Term::where('is_active', true)->first();

        return Inertia::render('Promotions/Index', [
            'academicYears' => $academicYears,
            'currentYear' => $currentYear,
            'activeTerm' => $activeTerm ? [
                'id' => $activeTerm->id,
                'name' => $activeTerm->name,
                'is_active' => $activeTerm->is_active,
            ] : null,
        ]);
    }

    public function getStudents($academicYearId, $classId)
    {
        $enrollments = StudentEnrollment::where([
            'academic_year_id' => $academicYearId,
            'school_class_id' => $classId,
        ])
            ->with('student', 'class')
            ->get()
            ->map(fn ($enrollment) => [
                'id' => $enrollment->id,
                'student_id' => $enrollment->student_id,
                'student_name' => $enrollment->student->full_name,
                'student_id_code' => $enrollment->student->student_id,
                'class_name' => $enrollment->class->name,
                'level' => $enrollment->class->level,
                'status' => $enrollment->status,
            ]);

        return response()->json($enrollments);
    }

    public function getClassStudents($classId)
    {
        $enrollments = StudentEnrollment::where('school_class_id', $classId)
            ->with('student', 'class', 'academicYear')
            ->get()
            ->map(fn ($enrollment) => [
                'id' => $enrollment->id,
                'student_id' => $enrollment->student_id,
                'student_name' => $enrollment->student->full_name,
                'student_id_code' => $enrollment->student->student_id,
                'class_name' => $enrollment->class->name,
                'level' => $enrollment->class->level,
                'status' => $enrollment->status,
                'academic_year' => $enrollment->academicYear->name,
            ]);

        return response()->json($enrollments);
    }

    public function getEnrollmentHistory($studentId)
    {
        $enrollments = StudentEnrollment::where('student_id', $studentId)
            ->with('class', 'academicYear')
            ->oldest('academic_year_id')
            ->get()
            ->map(fn ($enrollment) => [
                'academic_year' => $enrollment->academicYear->name,
                'class_name' => $enrollment->class->name,
                'level' => $enrollment->class->level,
                'status' => ucfirst($enrollment->status),
                'enrolled_date' => $enrollment->enrolled_at->format('M d, Y'),
            ]);

        return response()->json($enrollments);
    }
}
