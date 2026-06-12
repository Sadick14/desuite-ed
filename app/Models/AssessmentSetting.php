<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentSetting extends Model
{
    protected $fillable = ['term_id', 'grading_scale_id', 'ca_weight', 'exam_weight', 'ca_max_marks', 'exam_max_marks'];

    protected $casts = [
        'ca_weight' => 'decimal:2',
        'exam_weight' => 'decimal:2',
        'ca_max_marks' => 'decimal:2',
        'exam_max_marks' => 'decimal:2',
    ];

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function gradingScale(): BelongsTo
    {
        return $this->belongsTo(GradingScale::class);
    }

    public function studentMarks(): HasMany
    {
        return $this->hasMany(StudentMark::class);
    }
}
