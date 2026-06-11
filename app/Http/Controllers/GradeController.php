<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GradeController extends Controller
{
    public function index(Request $request)
    {
        $classes = SchoolClass::all();
        $terms = Term::with('academicYear')->get();
        $exams = Exam::with('course')->get();
        $courses = Course::all();

        $selectedClassId = $request->class_id;
        $selectedExamId = $request->exam_id;

        $students = collect();
        $grades = collect();

        if ($selectedClassId && $selectedExamId) {
            $exam = Exam::find($selectedExamId);
            $students = Student::where('school_class_id', $selectedClassId)
                ->where('active', true)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();

            $grades = Grade::where('exam_id', $selectedExamId)->get()->keyBy('student_id');
        }

        return Inertia::render('Grades/Index', [
            'classes' => $classes,
            'terms' => $terms,
            'exams' => $exams,
            'courses' => $courses,
            'students' => $students,
            'grades' => $grades,
            'selectedClassId' => $selectedClassId,
            'selectedExamId' => $selectedExamId,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'grades' => 'required|array',
        ]);

        foreach ($request->grades as $studentId => $data) {
            if (isset($data['score']) && $data['score'] !== null && $data['score'] !== '') {
                Grade::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'exam_id' => $request->exam_id,
                    ],
                    [
                        'course_id' => Exam::find($request->exam_id)->course_id,
                        'score' => $data['score'],
                        'remarks' => $data['remarks'] ?? null,
                    ]
                );
            } else {
                Grade::where('student_id', $studentId)->where('exam_id', $request->exam_id)->delete();
            }
        }

        return back()->with('success', 'Grades saved successfully!');
    }
}
