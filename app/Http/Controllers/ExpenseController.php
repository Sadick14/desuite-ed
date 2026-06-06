<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    public function index()
    {
        return Inertia::render('Expenses/Index', [
            'expenses' => Expense::with('category')->latest()->get(),
            'categories' => ExpenseCategory::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'title' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'expense_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:cash,momo,bank'],
        ]);

        $data['user_id'] = auth()->id();

        Expense::create($data);

        return redirect()->back();
    }

    public function update(Request $request, Expense $expense)
    {
        $data = $request->validate([
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'title' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'expense_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:cash,momo,bank'],
        ]);

        $data['user_id'] = auth()->id();

        $expense->update($data);

        return redirect()->back();
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->back();
    }
}
