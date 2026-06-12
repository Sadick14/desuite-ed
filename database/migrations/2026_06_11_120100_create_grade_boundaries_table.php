<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_boundaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grading_scale_id')->constrained('grading_scales')->onDelete('cascade');
            $table->decimal('min_score', 5, 2);
            $table->decimal('max_score', 5, 2);
            $table->string('grade'); // A, B, C, D, E, F
            $table->string('remark'); // Excellent, Good, Fair, etc.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_boundaries');
    }
};
