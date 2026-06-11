<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'exam_id',
        'course_id',
        'score',
        'remarks',
        'weighted_score',
        'final_grade',
        'final_remark',
    ];

    protected $appends = [
        'percentage',
        'letter_grade',
        'is_passing',
        'weighted_score',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Calculate percentage score
     */
    public function getPercentageAttribute(): float
    {
        if (! $this->exam || $this->exam->max_score == 0) {
            return 0;
        }

        return round(($this->score / $this->exam->max_score) * 100, 2);
    }

    /**
     * Calculate weighted score contribution
     */
    public function getWeightedScoreAttribute(): float
    {
        if (! $this->exam || $this->exam->max_score == 0) {
            return 0;
        }

        $percentage = $this->percentage;
        $weight = $this->exam->weight / 100;

        return round($percentage * $weight, 2);
    }

    /**
     * Get letter grade based on percentage
     */
    public function getLetterGradeAttribute(): string
    {
        $percentage = $this->percentage;

        if ($percentage >= 90) {
            return 'A+';
        }
        if ($percentage >= 80) {
            return 'A';
        }
        if ($percentage >= 70) {
            return 'B';
        }
        if ($percentage >= 60) {
            return 'C';
        }
        if ($percentage >= 50) {
            return 'D';
        }

        return 'F';
    }

    /**
     * Check if grade is passing
     */
    public function getIsPassingAttribute(): bool
    {
        if (! $this->exam) {
            return false;
        }

        return $this->score >= $this->exam->pass_score;
    }
}
