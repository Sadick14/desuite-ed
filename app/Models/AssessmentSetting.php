<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentSetting extends Model
{
    protected $fillable = [
        'academic_year_id',
        'term_id',
        'class_assessment_weight',
        'mid_term_weight',
        'quiz_weight',
        'final_exam_weight',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }
}
