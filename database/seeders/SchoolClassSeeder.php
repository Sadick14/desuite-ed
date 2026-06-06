<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SchoolClassSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = [
            // Nursery / Kindergarten (optional)
            ['name' => 'Nursery', 'level' => 'nursery'],
            ['name' => 'Kindergarten', 'level' => 'kindergarten'],

            // Lower Primary (grades 1-3)
            ['name' => 'Primary 1', 'level' => 'lower_primary'],
            ['name' => 'Primary 2', 'level' => 'lower_primary'],
            ['name' => 'Primary 3', 'level' => 'lower_primary'],

            // Upper Primary (grades 4-6)
            ['name' => 'Primary 4', 'level' => 'upper_primary'],
            ['name' => 'Primary 5', 'level' => 'upper_primary'],
            ['name' => 'Primary 6', 'level' => 'upper_primary'],

            // Junior High School (JHS)
            ['name' => 'JHS 1', 'level' => 'jhs'],
            ['name' => 'JHS 2', 'level' => 'jhs'],
            ['name' => 'JHS 3', 'level' => 'jhs'],
        ];

        foreach ($classes as $class) {
            SchoolClass::create($class);
        }
    }
}
