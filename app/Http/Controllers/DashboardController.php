<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentMark;
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
                'grades' => StudentMark::where('status', 'approved')->count(),
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
                    'grades' => StudentMark::count(),
                ];
                $data['recentGrades'] = StudentMark::with('student', 'course')
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(fn ($m) => [
                        'id' => $m->id,
                        'student' => $m->student?->first_name.' '.$m->student?->last_name ?? 'Unknown',
                        'course' => $m->course?->name ?? 'Unknown',
                        'final_score' => $m->final_score,
                        'letter_grade' => $m->letter_grade,
                        'date' => $m->created_at,
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
                    'gradeAverage' => StudentMark::where('status', 'approved')->avg('final_score') ?? 0,
                    'upcomingExams' => 0,
                ];
                $data['myGrades'] = StudentMark::with('course')
                    ->where('status', 'approved')
                    ->latest()
                    ->take(10)
                    ->get()
                    ->map(fn ($m) => [
                        'id' => $m->id,
                        'course' => $m->course?->name ?? 'Unknown',
                        'final_score' => $m->final_score,
                        'letter_grade' => $m->letter_grade,
                        'date' => $m->created_at,
                    ]);
                break;
        }

        return Inertia::render('Dashboard', $data);
    }
}
