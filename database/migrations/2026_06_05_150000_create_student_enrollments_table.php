<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['active', 'promoted', 'retained', 'transferred', 'withdrawn'])->default('active');
            $table->date('enrolled_at')->useCurrent();
            $table->timestamps();

            // One enrollment per student per academic year
            $table->unique(['student_id', 'academic_year_id']);

            // Index for querying by year and class
            $table->index(['academic_year_id', 'school_class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');
    }
};
