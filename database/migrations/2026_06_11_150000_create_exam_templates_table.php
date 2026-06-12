<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('exam_type'); // class_assessment, mid_term, quiz, final_exam
            $table->decimal('weight', 5, 2)->default(0);
            $table->decimal('max_score', 8, 2)->default(100);
            $table->decimal('pass_score', 8, 2)->default(50);
            $table->string('level')->nullable(); // nursery, kindergarten, lower_primary, upper_primary, jhs
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_templates');
    }
};
