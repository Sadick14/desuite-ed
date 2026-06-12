<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add daily_rate and is_recurring to fee_structures for feeding fees
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->decimal('daily_rate', 10, 2)->nullable()->after('amount')->comment('Daily rate for recurring fees (e.g., feeding fees)');
            $table->boolean('is_recurring')->default(false)->after('daily_rate')->comment('Whether this is a recurring weekly fee');
        });

        // Create table to track student feeding fee carryforward balance
        Schema::create('student_feeding_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();
            $table->integer('week_number')->comment('Week number of term (1-13 typical)');
            $table->decimal('carryforward_balance', 10, 2)->default(0)->comment('Amount carried forward from previous week');
            $table->integer('days_attended')->default(0)->comment('Days attended this week (Mon-Fri)');
            $table->decimal('amount_owed', 10, 2)->default(0)->comment('Amount owed for this week based on attendance');
            $table->decimal('amount_paid', 10, 2)->default(0)->comment('Amount paid this week');
            $table->timestamps();

            $table->unique(['student_id', 'term_id', 'week_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_feeding_balances');

        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropColumn(['daily_rate', 'is_recurring']);
        });
    }
};
