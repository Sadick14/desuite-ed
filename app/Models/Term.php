<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function feeStructures()
    {
        return $this->hasMany(FeeStructure::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function isExpired(): bool
    {
        return $this->end_date < today();
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isCurrently(): bool
    {
        return $this->start_date <= today() && $this->end_date >= today();
    }
}
