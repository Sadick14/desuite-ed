<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create the class_courses pivot table (many-to-many relationship)
        if (! Schema::hasTable('class_courses')) {
            Schema::create('class_courses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('class_id')->constrained('school_classes')->onDelete('cascade');
                $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
                $table->timestamps();

                // Ensure a course is only assigned once per class
                $table->unique(['class_id', 'course_id']);
            });
        }

        // 2. Remove the school_class_id from courses table to make it a global pool
        if (Schema::hasColumn('courses', 'school_class_id')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropForeign(['school_class_id']);
                $table->dropColumn('school_class_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('class_courses');

        // Restore school_class_id column if rolling back
        if (! Schema::hasColumn('courses', 'school_class_id')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->onDelete('cascade')->after('level');
            });
        }
    }
};
