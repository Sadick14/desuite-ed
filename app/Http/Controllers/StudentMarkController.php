<?php

namespace App\Http\Controllers;

use App\Models\AssessmentSetting;
use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentMark;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StudentMarkController extends Controller
{
    public function index(Request $request)
    {
        $terms = Term::all();
        $classes = SchoolClass::all();

        $selectedTermId = $request->term_id;
        $selectedClassId = $request->class_id;

        $marks = StudentMark::query()
            ->with('student', 'course', 'term')
            ->when($selectedTermId, fn ($q) => $q->where('term_id', $selectedTermId))
            ->when($selectedClassId, fn ($q) => $q->whereHas('student', fn ($sq) => $sq->where('school_class_id', $selectedClassId))
            )
            ->get();

        return Inertia::render('StudentMarks/Index', [
            'marks' => $marks,
            'terms' => $terms,
            'classes' => $classes,
            'selectedTermId' => $selectedTermId,
            'selectedClassId' => $selectedClassId,
        ]);
    }

    public function create(Request $request)
    {
        $classId = $request->class_id;
        $courseId = $request->course_id;
        $termId = $request->term_id;

        $class = SchoolClass::findOrFail($classId);
        $course = Course::findOrFail($courseId);
        $term = Term::findOrFail($termId);
        $setting = AssessmentSetting::where('term_id', $termId)->firstOrFail();

        // Get students in this class
        $students = Student::where('school_class_id', $classId)
            ->where('active', true)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return Inertia::render('StudentMarks/Create', [
            'term' => $term,
            'course' => $course,
            'class' => $class,
            'setting' => $setting,
            'students' => $students,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'term_id' => 'required|exists:terms,id',
            'course_id' => 'required|exists:courses,id',
            'class_id' => 'required|exists:school_classes,id',
            'marks' => 'required|array',
            'marks.*.student_id' => 'required|exists:students,id',
            'marks.*.class_test_1' => 'required|numeric|min:0|max:10',
            'marks.*.class_test_2' => 'required|numeric|min:0|max:10',
            'marks.*.class_test_3' => 'required|numeric|min:0|max:10',
            'marks.*.assignment' => 'required|numeric|min:0|max:20',
            'marks.*.classwork' => 'required|numeric|min:0|max:30',
            'marks.*.project' => 'required|numeric|min:0|max:20',
            'marks.*.exam_score' => 'required|numeric|min:0|max:100',
        ]);

        $setting = AssessmentSetting::where('term_id', $validated['term_id'])->firstOrFail();

        foreach ($validated['marks'] as $markData) {
            $mark = StudentMark::updateOrCreate(
                [
                    'student_id' => $markData['student_id'],
                    'course_id' => $validated['course_id'],
                    'term_id' => $validated['term_id'],
                ],
                [
                    'assessment_setting_id' => $setting->id,
                    'class_test_1' => $markData['class_test_1'] ?? null,
                    'class_test_2' => $markData['class_test_2'] ?? null,
                    'class_test_3' => $markData['class_test_3'] ?? null,
                    'assignment' => $markData['assignment'] ?? null,
                    'classwork' => $markData['classwork'] ?? null,
                    'project' => $markData['project'] ?? null,
                    'exam_score' => $markData['exam_score'] ?? null,
                    'status' => 'draft',
                ]
            );

            $mark->calculateGrade();
            $mark->save();
        }

        return redirect()->route('student-marks.index', [
            'term_id' => $validated['term_id'],
            'class_id' => $validated['class_id'],
        ])->with('success', 'Marks saved as draft');
    }

    public function submit(Request $request, StudentMark $studentMark)
    {
        $studentMark->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'submitted_by' => Auth::id(),
        ]);

        return back()->with('success', 'Marks submitted for approval');
    }

    public function approve(Request $request, StudentMark $studentMark)
    {
        $this->authorize('admin');

        $studentMark->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        return back()->with('success', 'Marks approved');
    }

    public function reject(Request $request, StudentMark $studentMark)
    {
        $this->authorize('admin');

        $validated = $request->validate(['reason' => 'required|string']);

        $studentMark->update([
            'status' => 'draft',
            'submitted_at' => null,
            'submitted_by' => null,
        ]);

        return back()->with('success', 'Marks returned for revision: '.$validated['reason']);
    }

    public function generateReports(Request $request)
    {
        $termId = $request->term_id;
        $term = Term::findOrFail($termId);

        $students = Student::with(['marks' => fn ($q) => $q->where('term_id', $termId)
            ->where('status', 'approved')
            ->with('course'),
        ])->get();

        return Inertia::render('StudentMarks/Reports', [
            'term' => $term,
            'students' => $students,
        ]);
    }

    public function showStudentGrades(Student $student)
    {
        $terms = Term::all();
        $marks = StudentMark::where('student_id', $student->id)
            ->with('course', 'term')
            ->get();

        return Inertia::render('StudentMarks/StudentGrades', [
            'student' => $student->load('class'),
            'terms' => $terms,
            'marks' => $marks,
        ]);
    }
}
