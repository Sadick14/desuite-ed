<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'school_class_id',
        'academic_year_id',
        'term_id',
        'attendance_date',
        'status',
        'notes',
        'sms_sent',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'sms_sent' => 'boolean',
    ];

    public const STATUS_PRESENT = 'present';

    public const STATUS_ABSENT = 'absent';

    public const STATUS_EXCUSED = 'excused';

    public const STATUS_LATE = 'late';

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }
}
