<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'level',
    ];

    public function classes()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_courses', 'course_id', 'class_id')
            ->withTimestamps();
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }
}
