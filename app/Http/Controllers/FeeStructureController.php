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
            'levels' => ['required', 'array', 'min:1'],
            'levels.*' => ['string', 'in:nursery,kindergarten,lower_primary,upper_primary,jhs'],
            'term_id' => ['required', 'exists:terms,id'],
            'fee_type' => ['required', 'in:school_fees,feeding_fees,registration_fees,others'],
            'fee_name' => ['nullable', 'string', 'max:255'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'daily_rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        $isFeeding = $data['fee_type'] === 'feeding_fees';

        // Create fee structure for each selected level
        foreach ($data['levels'] as $level) {
            FeeStructure::updateOrCreate(
                [
                    'level' => $level,
                    'term_id' => $data['term_id'],
                    'fee_type' => $data['fee_type'],
                ],
                [
                    'fee_name' => $data['fee_name'] ?? null,
                    'amount' => $isFeeding ? null : ($data['amount'] ?? 0),
                    'daily_rate' => $isFeeding ? ($data['daily_rate'] ?? 0) : null,
                    'is_recurring' => $isFeeding,
                ]
            );
        }

        return back();
    }

    public function update(Request $request, FeeStructure $feeStructure)
    {
        $data = $request->validate([
            'level' => ['required', 'string'],
            'term_id' => ['required', 'exists:terms,id'],
            'fee_type' => ['required', 'in:school_fees,feeding_fees,registration_fees,others'],
            'fee_name' => ['nullable', 'string', 'max:255'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'daily_rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        $isFeeding = $data['fee_type'] === 'feeding_fees';
        $data['amount'] = $isFeeding ? null : ($data['amount'] ?? 0);
        $data['daily_rate'] = $isFeeding ? ($data['daily_rate'] ?? 0) : null;
        $data['is_recurring'] = $isFeeding;

        $feeStructure->update($data);

        return redirect()->back();
    }

    public function destroy(FeeStructure $feeStructure)
    {
        $feeStructure->delete();

        return redirect()->back();
    }
}
