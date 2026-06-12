<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_marks', function (Blueprint $table) {
            // Add Test 3, Classwork, and Project columns
            if (! Schema::hasColumn('student_marks', 'class_test_3')) {
                $table->decimal('class_test_3', 5, 2)->nullable()->after('class_test_2');
            }
            if (! Schema::hasColumn('student_marks', 'classwork')) {
                $table->decimal('classwork')->nullable()->after('assignment');
            }
            if (! Schema::hasColumn('student_marks', 'project')) {
                $table->decimal('project', 5, 2)->nullable()->after('classwork');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_marks', function (Blueprint $table) {
            if (Schema::hasColumn('student_marks', 'class_test_3')) {
                $table->dropColumn('class_test_3');
            }
            if (Schema::hasColumn('student_marks', 'classwork')) {
                $table->dropColumn('classwork');
            }
            if (Schema::hasColumn('student_marks', 'project')) {
                $table->dropColumn('project');
            }
        });
    }
};
