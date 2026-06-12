<?php

namespace App\Http\Controllers;

use App\Models\AssessmentSetting;
use App\Models\GradingScale;
use App\Models\Term;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AssessmentSettingController extends Controller
{
    public function index()
    {
        $settings = AssessmentSetting::with('term', 'gradingScale')->get();
        $terms = Term::all();
        $scales = GradingScale::where('is_active', true)->get();

        return Inertia::render('AssessmentSettings/Index', [
            'settings' => $settings,
            'terms' => $terms,
            'scales' => $scales,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'term_id' => 'required|exists:terms,id|unique:assessment_settings',
            'grading_scale_id' => 'required|exists:grading_scales,id',
            'ca_weight' => 'required|numeric|min:0|max:100',
            'exam_weight' => 'required|numeric|min:0|max:100',
            'ca_max_marks' => 'required|numeric|min:0',
            'exam_max_marks' => 'required|numeric|min:0',
        ]);

        if ($validated['ca_weight'] + $validated['exam_weight'] != 100) {
            return back()->withErrors(['weights' => 'CA and Exam weights must sum to 100']);
        }

        AssessmentSetting::create($validated);

        return redirect()->route('assessment-settings.index')->with('success', 'Assessment settings created');
    }

    public function update(Request $request, AssessmentSetting $assessmentSetting)
    {
        $validated = $request->validate([
            'grading_scale_id' => 'required|exists:grading_scales,id',
            'ca_weight' => 'required|numeric|min:0|max:100',
            'exam_weight' => 'required|numeric|min:0|max:100',
            'ca_max_marks' => 'required|numeric|min:0',
            'exam_max_marks' => 'required|numeric|min:0',
        ]);

        if ($validated['ca_weight'] + $validated['exam_weight'] != 100) {
            return back()->withErrors(['weights' => 'CA and Exam weights must sum to 100']);
        }

        $assessmentSetting->update($validated);

        return redirect()->route('assessment-settings.index')->with('success', 'Assessment settings updated');
    }

    public function destroy(AssessmentSetting $assessmentSetting)
    {
        $assessmentSetting->delete();

        return redirect()->route('assessment-settings.index')->with('success', 'Assessment settings deleted');
    }
}
