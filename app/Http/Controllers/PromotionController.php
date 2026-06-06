<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromotionController extends Controller
{
    public function promote(Student $student, Request $request)
    {
        $validated = $request->validate([
            'from_academic_year_id' => 'required|exists:academic_years,id',
            'to_academic_year_id' => 'required|exists:academic_years,id',
            'new_class_id' => 'required|exists:school_classes,id',
            'status' => 'nullable|in:promoted,retained,transferred,withdrawn',
        ]);

        $toYear = AcademicYear::find($validated['to_academic_year_id']);
        $newClass = SchoolClass::find($validated['new_class_id']);
        $status = $validated['status'] ?? 'promoted';

        // Verify new class belongs to target academic year
        if ($newClass->academic_year_id !== $toYear->id) {
            return back()->withErrors(['new_class_id' => 'Class must belong to target academic year']);
        }

        DB::transaction(function () use ($student, $validated, $newClass, $status) {
            // Mark current enrollment with the action status
            $oldEnrollment = StudentEnrollment::where([
                'student_id' => $student->id,
                'academic_year_id' => $validated['from_academic_year_id'],
            ])->first();

            if ($oldEnrollment) {
                $oldEnrollment->update(['status' => $status]);
            }

            // Create new enrollment for next academic year
            StudentEnrollment::create([
                'student_id' => $student->id,
                'academic_year_id' => $validated['to_academic_year_id'],
                'school_class_id' => $newClass->id,
                'status' => 'active',
                'enrolled_at' => now(),
            ]);

            // Update student's current class
            $student->update(['school_class_id' => $newClass->id]);
        });

        return back()->with('success', "{$student->full_name} {$status} successfully");
    }

    public function promoteClass(SchoolClass $class, Request $request)
    {
        $validated = $request->validate([
            'to_academic_year_id' => 'required|exists:academic_years,id',
            'new_class_id' => 'required|exists:school_classes,id',
            'status' => 'nullable|in:promoted,retained,transferred,withdrawn',
        ]);

        $fromYear = $class->academicYear;
        $toYear = AcademicYear::find($validated['to_academic_year_id']);
        $newClass = SchoolClass::find($validated['new_class_id']);
        $status = $validated['status'] ?? 'promoted';

        // Verify new class belongs to target academic year
        if ($newClass->academic_year_id !== $toYear->id) {
            return back()->withErrors(['new_class_id' => 'Class must belong to target academic year']);
        }

        // Get all students in this class this year
        $enrollments = StudentEnrollment::where([
            'school_class_id' => $class->id,
            'academic_year_id' => $fromYear->id,
        ])->with('student')->get();

        $count = $enrollments->count();

        DB::transaction(function () use ($enrollments, $toYear, $newClass, $status) {
            foreach ($enrollments as $enrollment) {
                $enrollment->update(['status' => $status]);

                StudentEnrollment::create([
                    'student_id' => $enrollment->student_id,
                    'academic_year_id' => $toYear->id,
                    'school_class_id' => $newClass->id,
                    'status' => 'active',
                    'enrolled_at' => now(),
                ]);

                // Update student's current class
                $enrollment->student->update(['school_class_id' => $newClass->id]);
            }
        });

        return back()->with('success', "All {$count} students {$status} successfully");
    }

    public function retainStudent(Student $student, Request $request)
    {
        $validated = $request->validate([
            'from_academic_year_id' => 'required|exists:academic_years,id',
            'to_academic_year_id' => 'required|exists:academic_years,id',
            'same_class_id' => 'required|exists:school_classes,id',
        ]);

        $toYear = AcademicYear::find($validated['to_academic_year_id']);
        $sameClass = SchoolClass::find($validated['same_class_id']);

        // Verify class belongs to target academic year
        if ($sameClass->academic_year_id !== $toYear->id) {
            return back()->withErrors(['same_class_id' => 'Class must belong to target academic year']);
        }

        DB::transaction(function () use ($student, $validated, $sameClass) {
            // Mark enrollment as retained
            $oldEnrollment = StudentEnrollment::where([
                'student_id' => $student->id,
                'academic_year_id' => $validated['from_academic_year_id'],
            ])->first();

            if ($oldEnrollment) {
                $oldEnrollment->update(['status' => 'retained']);
            }

            // Create new enrollment in same class (but in new year if different)
            StudentEnrollment::create([
                'student_id' => $student->id,
                'academic_year_id' => $validated['to_academic_year_id'],
                'school_class_id' => $sameClass->id,
                'status' => 'active',
                'enrolled_at' => now(),
            ]);
        });

        return back()->with('success', "{$student->full_name} retained successfully");
    }

    public function processBulk(Request $request)
    {
        $validated = $request->validate([
            'from_academic_year_id' => 'required|exists:academic_years,id',
            'to_academic_year_id' => 'required|exists:academic_years,id',
            'promotions' => 'required|array',
            'promotions.*.student_id' => 'required|exists:students,id',
            'promotions.*.action' => 'required|in:promote,retain,withdraw,transfer',
            'promotions.*.target_class_id' => 'nullable|exists:school_classes,id',
        ]);

        $fromYearId = $validated['from_academic_year_id'];
        $toYearId = $validated['to_academic_year_id'];

        DB::transaction(function () use ($validated, $fromYearId, $toYearId) {
            foreach ($validated['promotions'] as $promo) {
                $student = Student::findOrFail($promo['student_id']);
                $action = $promo['action'];
                $targetClassId = $promo['target_class_id'] ?? null;

                // Mark current enrollment with the status
                $oldEnrollment = StudentEnrollment::where([
                    'student_id' => $student->id,
                    'academic_year_id' => $fromYearId,
                ])->first();

                $status = 'promoted';
                if ($action === 'retain') {
                    $status = 'retained';
                } elseif ($action === 'withdraw') {
                    $status = 'withdrawn';
                } elseif ($action === 'transfer') {
                    $status = 'transferred';
                }

                if ($oldEnrollment) {
                    $oldEnrollment->update(['status' => $status]);
                }

                if (in_array($action, ['promote', 'retain'])) {
                    if ($action === 'retain' && ! $targetClassId) {
                        // Find equivalent class in target academic year for retention
                        $currentClassModel = SchoolClass::find($oldEnrollment->school_class_id);
                        $equivalentClass = SchoolClass::where([
                            'academic_year_id' => $toYearId,
                            'name' => $currentClassModel->name,
                        ])->first();
                        $targetClassId = $equivalentClass ? $equivalentClass->id : $oldEnrollment->school_class_id;
                    }

                    if ($targetClassId) {
                        // Create new enrollment for target academic year
                        StudentEnrollment::create([
                            'student_id' => $student->id,
                            'academic_year_id' => $toYearId,
                            'school_class_id' => $targetClassId,
                            'status' => 'active',
                            'enrolled_at' => now(),
                        ]);

                        // Update student's current class
                        $student->update(['school_class_id' => $targetClassId]);
                    }
                }
            }
        });

        return back()->with('success', 'Cohort promotions processed successfully.');
    }
}
