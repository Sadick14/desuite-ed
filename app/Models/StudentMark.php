<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentMark extends Model
{
    protected $fillable = [
        'student_id', 'course_id', 'term_id', 'assessment_setting_id',
        'class_test_1', 'class_test_2', 'class_test_3', 'assignment', 'classwork', 'project',
        'exam_score', 'ca_total', 'final_score', 'letter_grade', 'remark',
        'rank', 'status', 'submitted_at', 'approved_at', 'submitted_by', 'approved_by',
    ];

    protected $casts = [
        'class_test_1' => 'decimal:2',
        'class_test_2' => 'decimal:2',
        'class_test_3' => 'decimal:2',
        'assignment' => 'decimal:2',
        'classwork' => 'decimal:2',
        'project' => 'decimal:2',
        'exam_score' => 'decimal:2',
        'ca_total' => 'decimal:2',
        'final_score' => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function assessmentSetting(): BelongsTo
    {
        return $this->belongsTo(AssessmentSetting::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function calculateGrade(): void
    {
        $setting = $this->assessmentSetting;
        $scale = $setting->gradingScale;

        // CA Structure (100 marks total):
        // Test 1: /10, Test 2: /10, Test 3: /10, Assignment: /20, Classwork: /30, Project: /20
        $ca_total =
            ($this->class_test_1 ?? 0) +    // /10
            ($this->class_test_2 ?? 0) +    // /10
            ($this->class_test_3 ?? 0) +    // /10
            ($this->assignment ?? 0) +      // /20
            ($this->classwork ?? 0) +       // /30
            ($this->project ?? 0);          // /20

        // CA is already out of 100, so it's the percentage
        $ca_percentage = min($ca_total, 100); // Cap at 100

        // Apply weights to get final score
        $ca_weight = $setting->ca_weight / 100;
        $exam_weight = $setting->exam_weight / 100;

        $weighted_ca = $ca_percentage * $ca_weight;
        $weighted_exam = $this->exam_score ? ($this->exam_score / 100) * $exam_weight : 0;

        $this->ca_total = round($ca_percentage, 2);
        $this->final_score = round($weighted_ca + $weighted_exam, 2);

        // Get grade from scale (final_score is 0-100)
        $boundary = $scale->getGrade($this->final_score);
        if ($boundary) {
            $this->letter_grade = $boundary->grade;
            $this->remark = $boundary->remark;
        }
    }
}
