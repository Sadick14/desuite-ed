<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function terms()
    {
        return $this->hasMany(Term::class);
    }

    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function classes()
    {
        return $this->hasMany(SchoolClass::class);
    }

    public function studentsByClass()
    {
        return $this->enrollments()
            ->with('student', 'class')
            ->groupBy('school_class_id');
    }

    public function getStudentsByLevel()
    {
        return $this->enrollments()
            ->with(['class', 'student'])
            ->get()
            ->groupBy(fn ($e) => $e->class->level);
    }
}
