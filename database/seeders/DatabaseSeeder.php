<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@school.local',
        ]);

        // Run all seeders
        $this->call([
            SchoolSeeder::class,
            AcademicYearSeeder::class,
            TermSeeder::class,
            SchoolClassSeeder::class,
            StudentSeeder::class,
            StudentEnrollmentSeeder::class,
            ExpenseCategorySeeder::class,
            FeeStructureSeeder::class,
            PaymentSeeder::class,
            ExpenseSeeder::class,
        ]);
    }
}
