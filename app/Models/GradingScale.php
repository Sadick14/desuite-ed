<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradingScale extends Model
{
    protected $fillable = ['name', 'description', 'is_active'];

    public function boundaries(): HasMany
    {
        return $this->hasMany(GradeBoundary::class);
    }

    public function assessmentSettings(): HasMany
    {
        return $this->hasMany(AssessmentSetting::class);
    }

    public function getGrade(float $score)
    {
        return $this->boundaries()
            ->where('min_score', '<=', $score)
            ->where('max_score', '>=', $score)
            ->first();
    }
}
