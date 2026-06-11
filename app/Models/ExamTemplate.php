<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'exam_type',
        'weight',
        'max_score',
        'pass_score',
        'level',
        'description',
    ];
}
