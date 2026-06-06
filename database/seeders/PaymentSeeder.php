<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = Student::all();
        $terms = Term::all();
        $user = User::first();
        $paymentMethods = ['cash', 'momo', 'bank'];
        $feeTypes = ['school_fees', 'feeding_fees', 'registration_fees', 'others'];

        $currentTerm = $terms->where('is_active', true)->first();

        if (! $currentTerm) {
            return;
        }

        // Create 150 random payments for testing
        for ($i = 0; $i < 150; $i++) {
            $student = $students->random();
            $feeType = $feeTypes[array_rand($feeTypes)];

            // Different amounts based on fee type
            $amounts = [
                'school_fees' => rand(200, 400),
                'feeding_fees' => rand(20, 40),
                'registration_fees' => rand(50, 100),
                'others' => rand(50, 150),
            ];

            Payment::create([
                'student_id' => $student->id,
                'term_id' => $currentTerm->id,
                'amount' => $amounts[$feeType],
                'payment_type' => $feeType,
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                'receipt_number' => 'RCP-'.now()->year.'-'.str_pad($i + 1, 5, '0', STR_PAD_LEFT),
                'payment_date' => now()->subDays(rand(0, 60))->toDateString(),
                'user_id' => $user->id,
            ]);
        }
    }
}
