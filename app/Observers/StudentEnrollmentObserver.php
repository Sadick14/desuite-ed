<?php

namespace App\Observers;

use App\Models\StudentEnrollment;

class StudentEnrollmentObserver
{
    public function creating(StudentEnrollment $enrollment): void
    {
        // Verify class belongs to academic year
        if ($enrollment->class->academic_year_id !== $enrollment->academic_year_id) {
            throw new \InvalidArgumentException('Class must belong to the specified academic year');
        }
    }

    public function created(StudentEnrollment $enrollment): void
    {
        // Prevent duplicate enrollments in same year
        StudentEnrollment::where([
            'student_id' => $enrollment->student_id,
            'academic_year_id' => $enrollment->academic_year_id,
        ])
            ->where('id', '!=', $enrollment->id)
            ->delete();
    }
}
