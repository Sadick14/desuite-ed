<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Term;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExamController extends Controller
{
    public function index(Request $request)
    {
        $exams = Exam::with(['course', 'term', 'academicYear'])->latest()->paginate(10);
        $courses = Course::all();
        $terms = Term::with('academicYear')->get();
        $academicYears = AcademicYear::all();

        return Inertia::render('Exams/Index', [
            'exams' => $exams,
            'courses' => $courses,
            'terms' => $terms,
            'academicYears' => $academicYears,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'exam_type' => 'required|string|max:255',
            'weight' => 'required|numeric|min:0|max:100',
            'course_id' => 'required|exists:courses,id',
            'term_id' => 'required|exists:terms,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'max_score' => 'nullable|numeric|min:1',
            'pass_score' => 'nullable|numeric|min:0',
            'date' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        Exam::create($request->all());

        return back()->with('success', 'Exam created successfully!');
    }

    public function update(Request $request, Exam $exam)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'exam_type' => 'required|string|max:255',
            'weight' => 'required|numeric|min:0|max:100',
            'course_id' => 'required|exists:courses,id',
            'term_id' => 'required|exists:terms,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'max_score' => 'nullable|numeric|min:1',
            'pass_score' => 'nullable|numeric|min:0',
            'date' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        $exam->update($request->all());

        return back()->with('success', 'Exam updated successfully!');
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();

        return back()->with('success', 'Exam deleted successfully!');
    }
}
