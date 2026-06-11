<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('grade_scale_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->string('grade'); // e.g., A, B, C, etc.
            $table->decimal('min_score', 5, 2); // e.g., 80
            $table->decimal('max_score', 5, 2); // e.g., 100
            $table->string('remark'); // e.g., Excellent, Very Good, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_scale_rules');
    }
};
