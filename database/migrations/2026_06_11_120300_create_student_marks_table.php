<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('term_id')->constrained('terms')->onDelete('cascade');
            $table->foreignId('assessment_setting_id')->constrained('assessment_settings')->onDelete('cascade');

            // Continuous Assessment Components
            $table->decimal('class_test_1', 5, 2)->nullable();
            $table->decimal('class_test_2', 5, 2)->nullable();
            $table->decimal('assignment', 5, 2)->nullable();
            $table->decimal('participation', 5, 2)->nullable();

            // Final Exam
            $table->decimal('exam_score', 5, 2)->nullable();

            // Calculated Fields
            $table->decimal('ca_total', 5, 2)->nullable(); // Total CA normalized
            $table->decimal('final_score', 5, 2)->nullable(); // Final combined score
            $table->string('letter_grade')->nullable(); // A, B, C, D, E, F
            $table->string('remark')->nullable(); // Excellent, Good, etc.
            $table->integer('rank')->nullable(); // Rank in class for this subject

            // Status
            $table->enum('status', ['draft', 'submitted', 'approved'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();

            // Audit
            $table->foreignId('submitted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();

            // Unique constraint per student per course per term
            $table->unique(['student_id', 'course_id', 'term_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_marks');
    }
};
