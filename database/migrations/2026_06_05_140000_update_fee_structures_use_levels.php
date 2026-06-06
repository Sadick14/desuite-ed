<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            // Drop the unique constraint first (before dropping the column)
            $table->dropUnique('fee_structures_term_id_school_class_id_fee_type_unique');
            // Drop the foreign key constraint
            $table->dropForeign(['school_class_id']);
            // Drop the column
            $table->dropColumn('school_class_id');
            // Add level column as enum
            $table->enum('level', ['nursery', 'kindergarten', 'lower_primary', 'upper_primary', 'jhs'])->after('term_id');
            // Create new unique constraint
            $table->unique(['term_id', 'level', 'fee_type']);
        });
    }

    public function down(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropUnique(['term_id', 'level', 'fee_type']);
            $table->dropColumn('level');
        });

        Schema::table('fee_structures', function (Blueprint $table) {
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->unique(['term_id', 'school_class_id', 'fee_type']);
        });
    }
};
