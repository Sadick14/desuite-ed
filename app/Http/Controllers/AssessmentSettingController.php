<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\AssessmentSetting;
use App\Models\Term;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AssessmentSettingController extends Controller
{
    public function index()
    {
        return Inertia::render('AssessmentSettings/Index', [
            'assessmentSettings' => AssessmentSetting::with(['academicYear', 'term'])->get(),
            'academicYears' => AcademicYear::all(),
            'terms' => Term::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'term_id' => ['required', 'exists:terms,id'],
            'class_assessment_weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'mid_term_weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'quiz_weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'final_exam_weight' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        AssessmentSetting::create($data);

        return redirect()->back();
    }

    public function update(Request $request, AssessmentSetting $assessmentSetting)
    {
        $data = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'term_id' => ['required', 'exists:terms,id'],
            'class_assessment_weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'mid_term_weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'quiz_weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'final_exam_weight' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $assessmentSetting->update($data);

        return redirect()->back();
    }

    public function destroy(AssessmentSetting $assessmentSetting)
    {
        $assessmentSetting->delete();

        return redirect()->back();
    }
}
