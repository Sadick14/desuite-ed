<?php

namespace App\Http\Controllers;

use App\Models\ExamTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExamTemplateController extends Controller
{
    public function index()
    {
        return Inertia::render('ExamTemplates/Index', [
            'examTemplates' => ExamTemplate::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'exam_type' => ['required', 'string'],
            'weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'max_score' => ['nullable', 'numeric', 'min:0'],
            'pass_score' => ['nullable', 'numeric', 'min:0'],
            'level' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        ExamTemplate::create($data);

        return back()->with('success', 'Exam template created successfully!');
    }

    public function update(Request $request, ExamTemplate $examTemplate)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'exam_type' => ['required', 'string'],
            'weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'max_score' => ['nullable', 'numeric', 'min:0'],
            'pass_score' => ['nullable', 'numeric', 'min:0'],
            'level' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $examTemplate->update($data);

        return back()->with('success', 'Exam template updated successfully!');
    }

    public function destroy(ExamTemplate $examTemplate)
    {
        $examTemplate->delete();

        return back()->with('success', 'Exam template deleted successfully!');
    }
}
