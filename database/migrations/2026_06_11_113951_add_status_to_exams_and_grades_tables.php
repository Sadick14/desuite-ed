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
        Schema::table('exams', function (Blueprint $table) {
            $table->enum('status', ['draft', 'submitted', 'approved', 'locked'])->default('draft');
        });

        Schema::table('grades', function (Blueprint $table) {
            $table->decimal('weighted_score', 5, 2)->nullable();
            $table->string('final_grade')->nullable();
            $table->string('final_remark')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn(['weighted_score', 'final_grade', 'final_remark']);
        });
    }
};
