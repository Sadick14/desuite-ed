<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $academicYears = AcademicYear::with('terms')->orderByDesc('is_active')->get();
        $currentYear = $academicYears->firstWhere('is_active', true) ?? $academicYears->first();
        $terms = $currentYear ? $currentYear->terms : collect();
        $currentTerm = $terms->firstWhere('is_active', true) ?? $terms->first();

        $selectedClassId = $request->class_id;
        $selectedDate = $request->date ?? now()->format('Y-m-d');
        $selectedTermId = $request->term_id ?? ($currentTerm ? $currentTerm->id : null);

        $classes = $currentYear ? $currentYear->classes : SchoolClass::all();

        $attendanceRecords = collect();
        $students = collect();

        if ($selectedClassId && $selectedTermId) {
            $schoolClass = SchoolClass::find($selectedClassId);
            $term = Term::find($selectedTermId);

            if ($schoolClass && $term) {
                $students = Student::where('school_class_id', $selectedClassId)
                    ->where('active', true)
                    ->orderBy('last_name')
                    ->orderBy('first_name')
                    ->get();

                $attendanceRecords = Attendance::where('school_class_id', $selectedClassId)
                    ->where('term_id', $selectedTermId)
                    ->where('attendance_date', $selectedDate)
                    ->get()
                    ->keyBy('student_id');
            }
        }

        return Inertia::render('Attendance/Index', [
            'academicYears' => $academicYears,
            'currentYear' => $currentYear,
            'terms' => $terms,
            'currentTerm' => $currentTerm,
            'classes' => $classes,
            'students' => $students,
            'attendanceRecords' => $attendanceRecords,
            'selectedClassId' => $selectedClassId,
            'selectedTermId' => $selectedTermId,
            'selectedDate' => $selectedDate,
        ]);
    }

    public function store(Request $request, SmsService $smsService)
    {
        $data = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'term_id' => ['required', 'exists:terms,id'],
            'attendance_date' => ['required', 'date'],
            'attendance' => ['required', 'array'],
            'attendance.*.student_id' => ['required', 'exists:students,id'],
            'attendance.*.status' => ['required', 'in:present,absent,excused,late'],
            'attendance.*.notes' => ['nullable', 'string'],
            'send_sms_alerts' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($data, $smsService) {
            $schoolClass = SchoolClass::find($data['school_class_id']);
            $term = Term::find($data['term_id']);
            $academicYear = $term->academicYear;

            foreach ($data['attendance'] as $record) {
                $student = Student::find($record['student_id']);

                $attendance = Attendance::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'attendance_date' => $data['attendance_date'],
                    ],
                    [
                        'school_class_id' => $schoolClass->id,
                        'academic_year_id' => $academicYear->id,
                        'term_id' => $term->id,
                        'status' => $record['status'],
                        'notes' => $record['notes'] ?? null,
                        'recorded_by' => auth()->id(),
                    ]
                );

                if (($record['status'] === 'absent') && ($data['send_sms_alerts'] ?? false) && ! $attendance->sms_sent && $student->parent_phone) {
                    try {
                        $smsService->sendSingleSms([
                            'student_id' => $student->id,
                            'phone' => $student->parent_phone,
                            'name' => $student->parent_name,
                            'message' => "Dear {$student->parent_name}, this is to inform you that {$student->first_name} {$student->last_name} was marked absent on {$data['attendance_date']}. Please contact the school if you have any questions. Thank you.",
                            'type' => 'attendance',
                        ]);

                        $attendance->update(['sms_sent' => true]);
                    } catch (\Exception $e) {
                        report($e);
                    }
                }
            }
        });

        return back()->with('success', 'Attendance recorded successfully!');
    }

    public function show(Student $student, Request $request)
    {
        $academicYears = AcademicYear::with('terms')->orderByDesc('is_active')->get();
        $currentYear = $academicYears->firstWhere('is_active', true) ?? $academicYears->first();
        $selectedYearId = $request->academic_year_id ?? ($currentYear ? $currentYear->id : null);

        $attendanceRecords = collect();

        if ($selectedYearId) {
            $attendanceRecords = Attendance::where('student_id', $student->id)
                ->where('academic_year_id', $selectedYearId)
                ->orderBy('attendance_date', 'desc')
                ->get();
        }

        $stats = [
            'present' => $attendanceRecords->where('status', 'present')->count(),
            'absent' => $attendanceRecords->where('status', 'absent')->count(),
            'excused' => $attendanceRecords->where('status', 'excused')->count(),
            'late' => $attendanceRecords->where('status', 'late')->count(),
        ];

        $total = $stats['present'] + $stats['absent'] + $stats['excused'] + $stats['late'];
        $attendanceRate = $total > 0 ? round(($stats['present'] / $total) * 100, 1) : 0;

        return Inertia::render('Attendance/Show', [
            'student' => $student,
            'academicYears' => $academicYears,
            'selectedYearId' => $selectedYearId,
            'attendanceRecords' => $attendanceRecords,
            'stats' => $stats,
            'attendanceRate' => $attendanceRate,
        ]);
    }
}
