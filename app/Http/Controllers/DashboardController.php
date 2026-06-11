<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Grade;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->role ?? 'member';

        // Default admin data
        $data = [
            'role' => $role,
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
        ];

        // Customize data based on role
        switch ($role) {
            case 'teacher':
                $data['stats'] = [
                    'students' => Student::count(),
                    'classes' => SchoolClass::count(),
                    'attendance' => Attendance::whereDate('date', today())->count(),
                    'grades' => Grade::count(),
                ];
                $data['recentGrades'] = Grade::with('student', 'exam')
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(fn ($g) => [
                        'id' => $g->id,
                        'student' => $g->student?->name ?? 'Unknown',
                        'exam' => $g->exam?->title ?? 'Unknown',
                        'score' => $g->score,
                        'date' => $g->created_at,
                    ]);
                $data['recentAttendance'] = Attendance::with('student')
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(fn ($a) => [
                        'id' => $a->id,
                        'student' => $a->student?->name ?? 'Unknown',
                        'status' => $a->status,
                        'date' => $a->date,
                    ]);
                break;
                
            case 'student':
            case 'parent':
                // For parent/student, we might filter to specific student later
                $data['stats'] = [
                    'attendancePercentage' => 0,
                    'gradeAverage' => Grade::avg('score') ?? 0,
                    'upcomingExams' => 0,
                ];
                $data['myGrades'] = Grade::with('exam')
                    ->latest()
                    ->take(10)
                    ->get()
                    ->map(fn ($g) => [
                        'id' => $g->id,
                        'exam' => $g->exam?->title ?? 'Unknown',
                        'score' => $g->score,
                        'date' => $g->created_at,
                    ]);
                break;
        }

        return Inertia::render('Dashboard', $data);
    }
}
