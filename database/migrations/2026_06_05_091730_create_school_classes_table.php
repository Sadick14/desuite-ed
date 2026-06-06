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
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            // e.g. "Primary 1", "JHS 2", "Nursery 1"

            $table->enum('level', [
                'nursery',
                'kindergarten',
                'lower_primary',   // Primary 1–3
                'upper_primary',   // Primary 4–6
                'jhs',
            ]);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_classes');
    }
};
