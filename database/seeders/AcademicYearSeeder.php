<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        AcademicYear::create([
            'name' => '2025/2026',
        ]);

        AcademicYear::create([
            'name' => '2024/2025',
            'ended_at' => now(),
        ]);
    }
}
