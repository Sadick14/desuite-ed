<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $courses = Course::latest()->paginate(10);

        return Inertia::render('Courses/Index', [
            'courses' => $courses,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'level' => 'nullable|string',
        ]);

        $course = Course::create($request->all());

        // Auto-create exams using templates
        $allYears = AcademicYear::latest()->get();
        $activeYear = $allYears->firstWhere(fn ($y) => $y->isActive());
        if ($activeYear) {
            $templates = ExamTemplate::where(function ($q) use ($course) {
                $q->whereNull('level')->orWhere('level', $course->level);
            })->get();

            foreach ($activeYear->terms as $term) {
                foreach ($templates as $template) {
                    Exam::create([
                        'name' => $template->name,
                        'exam_type' => $template->exam_type,
                        'weight' => $template->weight,
                        'max_score' => $template->max_score,
                        'pass_score' => $template->pass_score,
                        'description' => $template->description,
                        'course_id' => $course->id,
                        'term_id' => $term->id,
                        'academic_year_id' => $activeYear->id,
                        'status' => 'draft',
                    ]);
                }
            }
        }

        return back()->with('success', 'Course created successfully and exams auto-generated!');
    }

    public function update(Request $request, Course $course)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'level' => 'nullable|string',
        ]);

        $course->update($request->all());

        return back()->with('success', 'Course updated successfully!');
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return back()->with('success', 'Course deleted successfully!');
    }
}
