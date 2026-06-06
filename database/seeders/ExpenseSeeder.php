<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $categories = ExpenseCategory::all();
        $user = User::first();

        // Title suggestions per category
        $titlesByCategory = [
            'Staff Salaries' => ['September Salary', 'October Salary', 'November Salary', 'Bonus Payment', 'Overtime'],
            'Utilities' => ['Electricity Bill', 'Water Bill', 'Internet Subscription', 'Gas Bill'],
            'Maintenance' => ['Roof Repair', 'Classroom Painting', 'Desk Fixing', 'Plumbing Repair', 'AC Service'],
            'Supplies' => ['Chalk and Markers', 'Printing Paper', 'Books and Registers', 'Stationery', 'Cleaning Supplies'],
            'Transportation' => ['Fuel for Bus', 'Vehicle Maintenance', 'Driver Allowance', 'Tire Replacement'],
            'Meals' => ['Breakfast Supplies', 'Lunch Supplies', 'Drinks', 'Snacks'],
            'Health & Insurance' => ['Health Insurance', 'Medical Supplies', 'Staff Check-up', 'First Aid Kit'],
            'Training & Development' => ['Staff Workshop', 'Certification Course', 'Conference Attendance', 'Online Training'],
        ];

        $paymentMethods = ['cash', 'momo', 'bank'];

        // Create 80 expenses (10 per category)
        foreach ($categories as $category) {
            $titles = $titlesByCategory[$category->name] ?? ['Miscellaneous Expense'];

            for ($i = 0; $i < 10; $i++) {
                $title = $titles[array_rand($titles)];

                // Amount range based on category
                $amountRange = match ($category->name) {
                    'Staff Salaries' => [2000, 5000],
                    'Utilities' => [300, 800],
                    'Maintenance' => [500, 2000],
                    'Supplies' => [200, 1000],
                    'Transportation' => [100, 500],
                    'Meals' => [500, 1500],
                    'Health & Insurance' => [400, 1200],
                    'Training & Development' => [600, 2000],
                    default => [100, 1000],
                };

                Expense::create([
                    'expense_category_id' => $category->id,
                    'title' => $title,
                    'description' => null, // optional, can be left null
                    'amount' => rand($amountRange[0], $amountRange[1]),
                    'expense_date' => now()->subDays(rand(0, 90))->toDateString(),
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'user_id' => $user->id,
                ]);
            }
        }
    }
}
