<?php

namespace App\Models;

use App\Services\BalanceService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'school_class_id',
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
        'parent_name',
        'parent_phone',
        'address',
        'admission_date',
        'photo',
        'active',
        'medical_notes',
        'allergies',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
    ];

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function enrollment(AcademicYear $year)
    {
        return $this->enrollments()->where('academic_year_id', $year->id)->first();
    }

    public function currentEnrollment()
    {
        return $this->enrollments()
            ->whereHas('academicYear', fn ($q) => $q->where('is_active', true))
            ->first();
    }

    public function currentClass()
    {
        return $this->currentEnrollment()?->class;
    }

    public function academicHistory()
    {
        return $this->enrollments()
            ->with('academicYear', 'class')
            ->oldest('academic_year_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // AUTO STUDENT ID GENERATOR
    public static function generateStudentId(): string
    {
        $count = self::count() + 1;

        return 'SCH-'.str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    public function canEnrollNextTerm(Term $currentTerm): bool
    {
        return BalanceService::canEnrollNextTerm($this, $currentTerm);
    }

    public function canPromoteNextYear(): bool
    {
        return BalanceService::canPromoteNextYear($this);
    }

    public function getOutstandingBalance(AcademicYear $year): array
    {
        return BalanceService::outstandingBalanceForYear($this, $year);
    }

    public function getCurrentBalance(): array
    {
        $currentTerm = Term::where('is_active', true)->first();
        if (! $currentTerm) {
            return ['balance' => 0, 'breakdown' => []];
        }

        return BalanceService::forStudent($this, $currentTerm);
    }
}
