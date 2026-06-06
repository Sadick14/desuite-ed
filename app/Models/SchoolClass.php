<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'level',
        'academic_year_id',
    ];

    // In SchoolClass model
    protected $appends = ['level_label'];

    public function getLevelLabelAttribute(): string
    {
        return match ($this->level) {
            'nursery' => 'Nursery',
            'kindergarten' => 'Kindergarten',
            'lower_primary' => 'Lower Primary',
            'upper_primary' => 'Upper Primary',
            'jhs' => 'JHS',
            default => ucfirst($this->level),
        };
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    // feeStructures() removed — fee_structures table uses 'level' enum (not school_class_id)
    // Query FeeStructure::where('level', $class->level) directly when needed

    // Helper: group display
    public function levelLabel(): string
    {
        return match ($this->level) {
            'nursery' => 'Nursery',
            'kindergarten' => 'Kindergarten',
            'lower_primary' => 'Lower Primary',
            'upper_primary' => 'Upper Primary',
            'jhs' => 'JHS',
        };
    }
}
