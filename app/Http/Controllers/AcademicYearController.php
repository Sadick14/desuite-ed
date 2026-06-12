<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AcademicYearController extends Controller
{
    public function index()
    {
        return Inertia::render('AcademicYears/Index', [
            'years' => AcademicYear::with('terms')->withCount('terms')->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'terms' => ['sometimes', 'array'],
            'terms.*.name' => ['required_with:terms', 'string'],
            'terms.*.start_date' => ['required_with:terms', 'date'],
            'terms.*.end_date' => ['required_with:terms', 'date'],
            'terms.*.is_active' => ['sometimes', 'boolean'],
        ]);

        $terms = $data['terms'] ?? [];
        unset($data['terms']);

        $year = AcademicYear::create($data);

        // Deactivate all other terms if any new term is active
        $hasActiveTerm = collect($terms)->contains('is_active', true);
        if ($hasActiveTerm) {
            Term::where('is_active', true)->update(['is_active' => false]);
        }

        // Create terms for this year
        foreach ($terms as $term) {
            $term['academic_year_id'] = $year->id;
            Term::create($term);
        }

        return redirect()->back();
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'terms' => ['sometimes', 'array'],
            'terms.*.name' => ['required_with:terms', 'string'],
            'terms.*.start_date' => ['required_with:terms', 'date'],
            'terms.*.end_date' => ['required_with:terms', 'date'],
            'terms.*.is_active' => ['sometimes', 'boolean'],
        ]);

        $terms = $data['terms'] ?? [];
        unset($data['terms']);

        $academicYear->update($data);

        // Deactivate all other terms if any new term is active
        $hasActiveTerm = collect($terms)->contains('is_active', true);
        if ($hasActiveTerm) {
            Term::where('is_active', true)->update(['is_active' => false]);
        }

        // Delete old terms and create new ones
        $academicYear->terms()->delete();
        foreach ($terms as $term) {
            $term['academic_year_id'] = $academicYear->id;
            Term::create($term);
        }

        return redirect()->back();
    }

    public function destroy(AcademicYear $academicYear)
    {
        $academicYear->delete();

        return redirect()->back();
    }

    public function endYear(AcademicYear $academicYear)
    {
        if ($academicYear->isEnded()) {
            return redirect()->back()->with('error', 'This academic year is already ended');
        }

        $academicYear->endYear();

        return redirect()->back()->with('success', "Academic year '{$academicYear->name}' has been closed. All data is now archived.");
    }
}
