<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'term_id',
        'level',
        'fee_type',
        'fee_name',
        'amount',
        'daily_rate',
        'is_recurring',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'is_recurring' => 'boolean',
    ];

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function getDisplayNameAttribute()
    {
        if ($this->fee_name) {
            return $this->fee_name;
        }

        $typeNames = [
            'school_fees' => 'School Fees',
            'feeding_fees' => 'Feeding Fees',
            'registration_fees' => 'Registration Fees',
            'others' => 'Others',
        ];

        return $typeNames[$this->fee_type] ?? $this->fee_type;
    }
}
