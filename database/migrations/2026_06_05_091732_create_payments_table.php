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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();

            $table->decimal('amount', 10, 2);

            $table->enum('payment_type', [
                'school_fees',
                'feeding_fees',
                'registration_fees',
                'others',
            ]);

            $table->enum('payment_method', [
                'cash',
                'momo',
                'bank',
            ]);

            $table->string('receipt_number')->unique();

            $table->date('payment_date');

            $table->foreignId('user_id')->constrained();

            $table->timestamps();

            $table->index(['student_id', 'term_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
