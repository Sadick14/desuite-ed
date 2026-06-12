<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SchoolClassController extends Controller
{
    public function index()
    {
        return Inertia::render('Classes/Index', [
            'classes' => SchoolClass::with('courses')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Classes/Create', [
            'courses' => Course::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'level' => 'required|string',
            'selected_courses' => 'required|array|min:1',
            'selected_courses.*' => 'exists:courses,id',
        ]);

        $class = SchoolClass::create([
            'name' => $validated['name'],
            'level' => $validated['level'],
        ]);

        // Attach selected courses to the class (pivot table)
        $class->courses()->attach($validated['selected_courses']);

        return redirect()->route('classes.index')->with('success', 'Class created with assigned courses successfully!');
    }

    public function edit(SchoolClass $class)
    {
        return Inertia::render('Classes/Edit', [
            'class' => $class->load('courses'),
            'courses' => Course::orderBy('name')->get(),
            'selectedCourseIds' => $class->courses()->pluck('course_id')->toArray(),
        ]);
    }

    public function update(Request $request, SchoolClass $class)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'level' => 'required|string',
            'selected_courses' => 'required|array|min:1',
            'selected_courses.*' => 'exists:courses,id',
        ]);

        $class->update([
            'name' => $validated['name'],
            'level' => $validated['level'],
        ]);

        // Sync courses (removes old, adds new)
        $class->courses()->sync($validated['selected_courses']);

        return redirect()->route('classes.index')->with('success', 'Class updated successfully!');
    }

    public function destroy(SchoolClass $class)
    {
        $class->delete();

        return redirect()->back()->with('success', 'Class deleted successfully!');
    }

    public function duplicate(SchoolClass $class)
    {
        // Create a new class with the same level and academic year
        $duplicatedClass = SchoolClass::create([
            'name' => $class->name.' (Copy)',
            'level' => $class->level,
            'academic_year_id' => $class->academic_year_id,
        ]);

        // Copy all courses from the original class
        $courseIds = $class->courses()->pluck('course_id')->toArray();
        if (! empty($courseIds)) {
            $duplicatedClass->courses()->attach($courseIds);
        }

        // Redirect to edit page with success message
        return redirect()
            ->route('classes.edit', ['class' => $duplicatedClass->id])
            ->with('success', 'Class duplicated! Update the name and save.');
    }
}
