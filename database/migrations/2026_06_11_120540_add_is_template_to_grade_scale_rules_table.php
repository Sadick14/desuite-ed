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
        Schema::table('grade_scale_rules', function (Blueprint $table) {
            $table->boolean('is_template')->default(false);
            $table->string('level')->nullable();
            $table->string('template_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grade_scale_rules', function (Blueprint $table) {
            $table->dropColumn(['is_template', 'level', 'template_name']);
        });
    }
};
