<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $team = $request->user()->currentTeam();

        return Inertia::render('Dashboard', [
            'stats' => [
                'students' => Student::count(),
                'classes' => SchoolClass::count(),
                'payments' => Payment::sum('amount'),
                'expenses' => Expense::sum('amount'),
            ],

            'recentPayments' => Payment::with('student')
                ->latest()
                ->take(5)
                ->get()
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'student' => $p->student?->name ?? 'Unknown',
                    'amount' => $p->amount,
                    'date' => $p->created_at,
                ]),

            'recentExpenses' => Expense::latest()
                ->take(5)
                ->get()
                ->map(fn ($e) => [
                    'id' => $e->id,
                    'description' => $e->description,
                    'amount' => $e->amount,
                    'date' => $e->created_at,
                ]),
        ]);
    }
}
