<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TermController extends Controller
{
    public function index()
    {
        return Inertia::render('Terms/Index', [
            'terms' => Term::with('academicYear')->latest()->get(),
            'years' => AcademicYear::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'name' => ['required', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'is_active' => ['boolean'],
        ]);

        // If this term is set as active, deactivate all other terms
        if (isset($data['is_active']) && $data['is_active']) {
            Term::where('is_active', true)->update(['is_active' => false]);
        }

        Term::create($data);

        return redirect()->back();
    }

    public function update(Request $request, Term $term)
    {
        $data = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'name' => ['required', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'is_active' => ['boolean'],
        ]);

        // If this term is being set to active, deactivate all other terms
        if (isset($data['is_active']) && $data['is_active']) {
            Term::where('id', '!=', $term->id)->where('is_active', true)->update(['is_active' => false]);
        }

        $term->update($data);

        return redirect()->back();
    }

    public function destroy(Term $term)
    {
        $term->delete();

        return redirect()->back();
    }
}
