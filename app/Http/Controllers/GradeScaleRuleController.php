<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\GradeScaleRule;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GradeScaleRuleController extends Controller
{
    public function index()
    {
        return Inertia::render('GradeScaleRules/Index', [
            'gradeScaleRules' => GradeScaleRule::with('academicYear')->get(),
            'academicYears' => AcademicYear::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'grade' => ['required'],
            'min_score' => ['required', 'numeric', 'min:0'],
            'max_score' => ['required', 'numeric', 'max:100'],
            'remark' => ['required'],
        ]);

        GradeScaleRule::create($data);

        return redirect()->back();
    }

    public function update(Request $request, GradeScaleRule $gradeScaleRule)
    {
        $data = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'grade' => ['required'],
            'min_score' => ['required', 'numeric', 'min:0'],
            'max_score' => ['required', 'numeric', 'max:100'],
            'remark' => ['required'],
        ]);

        $gradeScaleRule->update($data);

        return redirect()->back();
    }

    public function destroy(GradeScaleRule $gradeScaleRule)
    {
        $gradeScaleRule->delete();

        return redirect()->back();
    }
}
