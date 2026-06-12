<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentFeedingBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'term_id',
        'week_number',
        'carryforward_balance',
        'days_attended',
        'amount_owed',
        'amount_paid',
    ];

    protected $casts = [
        'carryforward_balance' => 'decimal:2',
        'amount_owed' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function getOutstandingBalanceAttribute()
    {
        return $this->carryforward_balance + $this->amount_owed - $this->amount_paid;
    }

    public function getCarryforwardToNextWeekAttribute()
    {
        $outstanding = $this->outstanding_balance;

        return $outstanding > 0 ? $outstanding : 0;
    }
}
