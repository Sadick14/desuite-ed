<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $categories = [
            ['name' => 'Staff Salaries'],
            ['name' => 'Utilities'],
            ['name' => 'Maintenance'],
            ['name' => 'Supplies'],
            ['name' => 'Transportation'],
            ['name' => 'Meals'],
            ['name' => 'Health & Insurance'],
            ['name' => 'Training & Development'],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::create($category);
        }
    }
}
