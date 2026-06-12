<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old assessment_settings table if it exists
        Schema::dropIfExists('assessment_settings');

        // Create the new assessment_settings table with the correct schema
        Schema::create('assessment_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('term_id')->constrained('terms')->onDelete('cascade');
            $table->foreignId('grading_scale_id')->constrained('grading_scales')->onDelete('cascade');
            $table->decimal('ca_weight', 5, 2)->default(40);
            $table->decimal('exam_weight', 5, 2)->default(60);
            $table->decimal('ca_max_marks', 5, 2)->default(100);
            $table->decimal('exam_max_marks', 5, 2)->default(100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_settings');
    }
};
