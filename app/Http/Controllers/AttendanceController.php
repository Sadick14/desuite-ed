<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $activeTerm = Term::where('is_active', true)->with('academicYear')->first();
        $classes = SchoolClass::with('students')->get();
        $selectedClass = null;
        $attendanceDate = $request->query('date', now()->toDateString());

        if ($request->has('class_id')) {
            $selectedClass = SchoolClass::with('students')->findOrFail($request->query('class_id'));
        }

        $attendanceRecords = Attendance::where('attendance_date', $attendanceDate);

        if ($selectedClass) {
            $attendanceRecords = $attendanceRecords->where('school_class_id', $selectedClass->id);
        }

        $attendanceRecords = $attendanceRecords->with('student', 'schoolClass', 'term')
            ->orderBy('school_class_id')
            ->orderBy('student_id')
            ->get();

        return Inertia::render('Attendance/Index', [
            'classes' => $classes,
            'selectedClass' => $selectedClass,
            'activeTerm' => $activeTerm,
            'attendanceRecords' => $attendanceRecords,
            'attendanceDate' => $attendanceDate,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'attendance_date' => ['required', 'date'],
            'status' => ['required', 'in:present,absent,excused,late'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $activeTerm = Term::where('is_active', true)->first();

        if (!$activeTerm) {
            return back()->withErrors(['term' => 'No active term found']);
        }

        $activateYear = $activeTerm->academicYear;

        $attendance = Attendance::updateOrCreate(
            [
                'student_id' => $data['student_id'],
                'school_class_id' => $data['school_class_id'],
                'attendance_date' => $data['attendance_date'],
                'term_id' => $activeTerm->id,
            ],
            [
                'academic_year_id' => $activateYear->id,
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
            ]
        );

        return back()->with('success', 'Attendance recorded successfully');
    }

    public function bulkStore(Request $request)
    {
        $data = $request->validate([
            'attendance_date' => ['required', 'date'],
            'class_id' => ['required', 'exists:school_classes,id'],
            'attendances' => ['required', 'array', 'min:1'],
            'attendances.*.student_id' => ['required', 'exists:students,id'],
            'attendances.*.status' => ['required', 'in:present,absent,excused,late'],
            'attendances.*.notes' => ['nullable', 'string', 'max:500'],
        ]);

        $activeTerm = Term::where('is_active', true)->first();

        if (!$activeTerm) {
            return back()->withErrors(['term' => 'No active term found']);
        }

        $academicYear = $activeTerm->academicYear;

        DB::transaction(function () use ($data, $activeTerm, $academicYear) {
            foreach ($data['attendances'] as $attendance) {
                Attendance::updateOrCreate(
                    [
                        'student_id' => $attendance['student_id'],
                        'attendance_date' => $data['attendance_date'],
                    ],
                    [
                        'school_class_id' => $data['class_id'],
                        'academic_year_id' => $academicYear->id,
                        'term_id' => $activeTerm->id,
                        'status' => $attendance['status'],
                        'notes' => $attendance['notes'] ?? null,
                    ]
                );
            }
        });

        return back()->with('success', 'Attendance records saved successfully');
    }

    public function history(Request $request)
    {
        $activeTerm = Term::where('is_active', true)->with('academicYear')->first();
        $classes = SchoolClass::get();

        $query = Attendance::with('student', 'schoolClass', 'term', 'academicYear');

        if ($request->has('class_id')) {
            $query = $query->where('school_class_id', $request->query('class_id'));
        }

        if ($request->has('student_id')) {
            $query = $query->where('student_id', $request->query('student_id'));
        }

        if ($request->has('start_date')) {
            $query = $query->whereDate('attendance_date', '>=', $request->query('start_date'));
        }

        if ($request->has('end_date')) {
            $query = $query->whereDate('attendance_date', '<=', $request->query('end_date'));
        }

        if ($request->has('status')) {
            $query = $query->where('status', $request->query('status'));
        }

        $attendance = $query->orderByDesc('attendance_date')
            ->orderBy('school_class_id')
            ->paginate(50);

        return Inertia::render('Attendance/History', [
            'attendance' => $attendance,
            'classes' => $classes,
            'activeTerm' => $activeTerm,
            'filters' => $request->only(['class_id', 'student_id', 'start_date', 'end_date', 'status']),
        ]);
    }

    public function report(Request $request)
    {
        $activeTerm = Term::where('is_active', true)->with('academicYear')->first();
        $classes = SchoolClass::with('students')->get();

        $classId = $request->query('class_id');
        $termId = $request->query('term_id', $activeTerm?->id);

        $selectedClass = $classId ? SchoolClass::with('students')->findOrFail($classId) : null;
        $selectedTerm = $termId ? Term::findOrFail($termId) : $activeTerm;

        $attendanceStats = [];

        if ($selectedClass) {
            $studentIds = $selectedClass->students()->pluck('students.id');

            $totalDays = Attendance::whereIn('student_id', $studentIds)
                ->where('term_id', $selectedTerm->id)
                ->distinct('attendance_date')
                ->count('attendance_date');

            foreach ($selectedClass->students as $student) {
                $present = Attendance::where('student_id', $student->id)
                    ->where('term_id', $selectedTerm->id)
                    ->where('status', 'present')
                    ->count();

                $absent = Attendance::where('student_id', $student->id)
                    ->where('term_id', $selectedTerm->id)
                    ->where('status', 'absent')
                    ->count();

                $late = Attendance::where('student_id', $student->id)
                    ->where('term_id', $selectedTerm->id)
                    ->where('status', 'late')
                    ->count();

                $excused = Attendance::where('student_id', $student->id)
                    ->where('term_id', $selectedTerm->id)
                    ->where('status', 'excused')
                    ->count();

                $attendanceStats[] = [
                    'student_id' => $student->id,
                    'student_name' => "{$student->first_name} {$student->last_name}",
                    'student_number' => $student->student_id,
                    'present' => $present,
                    'absent' => $absent,
                    'late' => $late,
                    'excused' => $excused,
                    'total_days' => $totalDays,
                    'attendance_percentage' => $totalDays > 0 ? round(($present / $totalDays) * 100, 2) : 0,
                ];
            }
        }

        $terms = Term::all();

        return Inertia::render('Attendance/Report', [
            'classes' => $classes,
            'selectedClass' => $selectedClass,
            'selectedTerm' => $selectedTerm,
            'terms' => $terms,
            'activeTerm' => $activeTerm,
            'attendanceStats' => $attendanceStats,
        ]);
    }

    public function analytics(Request $request)
    {
        $activeTerm = Term::where('is_active', true)->with('academicYear')->first();
        $classes = SchoolClass::get();

        $classId = $request->query('class_id');
        $termId = $request->query('term_id', $activeTerm?->id);

        $selectedClass = $classId ? SchoolClass::with('students')->findOrFail($classId) : null;
        $selectedTerm = $termId ? Term::findOrFail($termId) : $activeTerm;

        $overallStats = [
            'total_present' => 0,
            'total_absent' => 0,
            'total_late' => 0,
            'total_excused' => 0,
            'average_attendance' => 0,
        ];

        $dailyStats = [];
        $classStats = [];

        if ($selectedTerm) {
            $query = Attendance::where('term_id', $selectedTerm->id);

            if ($selectedClass) {
                $query = $query->where('school_class_id', $selectedClass->id);
            }

            $attendances = $query->get();

            foreach ($attendances as $attendance) {
                switch ($attendance->status) {
                    case 'present':
                        $overallStats['total_present']++;
                        break;
                    case 'absent':
                        $overallStats['total_absent']++;
                        break;
                    case 'late':
                        $overallStats['total_late']++;
                        break;
                    case 'excused':
                        $overallStats['total_excused']++;
                        break;
                }

                $dateStr = $attendance->attendance_date->toDateString();
                if (!isset($dailyStats[$dateStr])) {
                    $dailyStats[$dateStr] = [
                        'date' => $dateStr,
                        'present' => 0,
                        'absent' => 0,
                        'late' => 0,
                        'excused' => 0,
                    ];
                }
                $dailyStats[$dateStr][$attendance->status]++;

                $classKey = $attendance->schoolClass->name;
                if (!isset($classStats[$classKey])) {
                    $classStats[$classKey] = [
                        'class' => $classKey,
                        'present' => 0,
                        'absent' => 0,
                        'late' => 0,
                        'excused' => 0,
                    ];
                }
                $classStats[$classKey][$attendance->status]++;
            }

            $total = $overallStats['total_present'] + $overallStats['total_absent'] + $overallStats['total_late'] + $overallStats['total_excused'];
            $overallStats['average_attendance'] = $total > 0 ? round(($overallStats['total_present'] / $total) * 100, 2) : 0;
        }

        $terms = Term::all();
        $dailyStats = array_values(collect($dailyStats)->sortBy('date')->all());
        $classStats = array_values($classStats);

        return Inertia::render('Attendance/Analytics', [
            'classes' => $classes,
            'selectedClass' => $selectedClass,
            'selectedTerm' => $selectedTerm,
            'terms' => $terms,
            'activeTerm' => $activeTerm,
            'overallStats' => $overallStats,
            'dailyStats' => $dailyStats,
            'classStats' => $classStats,
        ]);
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return back()->with('success', 'Attendance record deleted');
    }
}
