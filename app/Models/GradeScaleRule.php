<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeScaleRule extends Model
{
    protected $fillable = [
        'academic_year_id',
        'grade',
        'min_score',
        'max_score',
        'remark',
        'is_template',
        'level',
        'template_name',
    ];

    protected $casts = [
        'is_template' => 'boolean',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
