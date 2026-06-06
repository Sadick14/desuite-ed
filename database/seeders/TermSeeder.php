<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TermSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $academicYear = AcademicYear::where('name', '2024/2025')->first();

        Term::create([
            'name' => 'Term 1',
            'academic_year_id' => $academicYear->id,
            'start_date' => '2024-09-01',
            'end_date' => '2024-11-30',
            'is_active' => true,
        ]);

        Term::create([
            'name' => 'Term 2',
            'academic_year_id' => $academicYear->id,
            'start_date' => '2024-12-01',
            'end_date' => '2025-02-28',
            'is_active' => false,
        ]);

        Term::create([
            'name' => 'Term 3',
            'academic_year_id' => $academicYear->id,
            'start_date' => '2025-03-01',
            'end_date' => '2025-06-30',
            'is_active' => false,
        ]);
    }
}
