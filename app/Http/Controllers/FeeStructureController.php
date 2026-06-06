<?php

namespace App\Http\Controllers;

use App\Models\FeeStructure;
use App\Models\Term;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeeStructureController extends Controller
{
    public function index()
    {
        return Inertia::render('FeeStructures/Index', [
            'fees' => FeeStructure::with('term')->latest()->get(),
            'terms' => Term::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'level' => ['required', 'string'],
            'term_id' => ['required', 'exists:terms,id'],
            'fee_type' => ['required', 'in:school_fees,feeding_fees,registration_fees,others'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        FeeStructure::updateOrCreate(
            [
                'level' => $data['level'],
                'term_id' => $data['term_id'],
                'fee_type' => $data['fee_type'],
            ],
            [
                'amount' => $data['amount'],
            ]
        );

        return back();
    }

    public function update(Request $request, FeeStructure $feeStructure)
    {
        $data = $request->validate([
            'level' => ['required', 'string'],
            'term_id' => ['required', 'exists:terms,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'fee_type' => ['required', 'in:school_fees,feeding_fees,registration_fees,others'],
        ]);

        $feeStructure->update($data);

        return redirect()->back();
    }

    public function destroy(FeeStructure $feeStructure)
    {
        $feeStructure->delete();

        return redirect()->back();
    }
}
