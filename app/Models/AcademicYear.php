<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ended_at',
    ];

    protected $casts = [
        'ended_at' => 'datetime',
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

    public function isActive(): bool
    {
        return $this->terms()->where('is_active', true)->exists();
    }

    public function isEnded(): bool
    {
        return $this->ended_at !== null;
    }

    public function endYear(): void
    {
        $this->update(['ended_at' => now()]);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('ended_at');
    }

    public function scopeEnded($query)
    {
        return $query->whereNotNull('ended_at');
    }
}
