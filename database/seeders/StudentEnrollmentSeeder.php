<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Database\Seeder;

class StudentEnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        // Get the active academic year (or most recent)
        $academicYears = AcademicYear::latest('created_at')->get();
        $academicYear = $academicYears->firstWhere(fn ($y) => $y->isActive()) ?? $academicYears->first();

        if (! $academicYear) {
            $this->command->info('No academic year found. Create one first.');

            return;
        }

        // Get all students that don't have an enrollment yet for this year
        $students = Student::whereDoesntHave('enrollments', fn ($q) => $q->where('academic_year_id', $academicYear->id)
        )->get();

        $this->command->info("Creating enrollments for {$students->count()} students...");

        foreach ($students as $student) {
            if ($student->school_class_id) {
                StudentEnrollment::create([
                    'student_id' => $student->id,
                    'academic_year_id' => $academicYear->id,
                    'school_class_id' => $student->school_class_id,
                    'status' => 'active',
                    'enrolled_at' => $student->admission_date ?? now(),
                ]);

                $this->command->line("✓ {$student->full_name}");
            }
        }

        $this->command->info('Enrollment seeding completed!');
    }
}
