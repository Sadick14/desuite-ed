<?php

namespace App\Http\Controllers;

use App\Models\GradeBoundary;
use App\Models\GradingScale;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GradingScaleController extends Controller
{
    public function index()
    {
        $scales = GradingScale::with('boundaries')->get();

        return Inertia::render('GradingScales/Index', ['scales' => $scales]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:grading_scales',
            'description' => 'nullable|string',
            'boundaries' => 'required|array|min:1',
            'boundaries.*.min_score' => 'required|numeric|min:0|max:100',
            'boundaries.*.max_score' => 'required|numeric|min:0|max:100',
            'boundaries.*.grade' => 'required|string|max:2',
            'boundaries.*.remark' => 'required|string',
        ]);

        $scale = GradingScale::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'is_active' => true,
        ]);

        foreach ($validated['boundaries'] as $boundary) {
            GradeBoundary::create([
                'grading_scale_id' => $scale->id,
                'min_score' => $boundary['min_score'],
                'max_score' => $boundary['max_score'],
                'grade' => $boundary['grade'],
                'remark' => $boundary['remark'],
            ]);
        }

        return redirect()->route('grading-scales.index')->with('success', 'Grading scale created');
    }

    public function show(GradingScale $gradingScale)
    {
        return redirect()->route('grading-scales.index');
    }

    public function update(Request $request, GradingScale $gradingScale)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:grading_scales,name,'.$gradingScale->id,
            'description' => 'nullable|string',
            'boundaries' => 'required|array|min:1',
            'boundaries.*.min_score' => 'required|numeric|min:0|max:100',
            'boundaries.*.max_score' => 'required|numeric|min:0|max:100',
            'boundaries.*.grade' => 'required|string|max:2',
            'boundaries.*.remark' => 'required|string',
        ]);

        $gradingScale->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);

        $gradingScale->boundaries()->delete();
        foreach ($validated['boundaries'] as $boundary) {
            GradeBoundary::create([
                'grading_scale_id' => $gradingScale->id,
                'min_score' => $boundary['min_score'],
                'max_score' => $boundary['max_score'],
                'grade' => $boundary['grade'],
                'remark' => $boundary['remark'],
            ]);
        }

        return redirect()->route('grading-scales.index')->with('success', 'Grading scale updated');
    }

    public function destroy(GradingScale $gradingScale)
    {
        $gradingScale->delete();

        return redirect()->route('grading-scales.index')->with('success', 'Grading scale deleted');
    }
}
