<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $firstNames = ['Kofi', 'Ama', 'Kwame', 'Akosua', 'Yaw', 'Abena', 'Kwesi', 'Esi', 'Owusu', 'Asare', 'Benjamin', 'Faridah', 'Amoah', 'Grace', 'Mensah'];
        $lastNames = ['Mensah', 'Owusu', 'Asante', 'Boateng', 'Amponsah', 'Adom', 'Nyarko', 'Anane', 'Adu', 'Agyemang', 'Osei', 'Appiah'];
        $parentNames = ['Mr. John Mensah', 'Mrs. Mary Owusu', 'Mr. Samuel Boateng', 'Mrs. Rose Amponsah', 'Mr. David Adom', 'Mrs. Patricia Nyarko'];
        $phones = ['0244123456', '0244123457', '0244123458', '0244123459', '0244123460', '0244123461', '0244123462', '0244123463', '0244123464', '0244123465'];

        $classes = SchoolClass::all();
        $counter = 1;

        // Create 60 students across all classes
        foreach ($classes as $class) {
            for ($i = 0; $i < 6; $i++) {
                $firstName = $firstNames[array_rand($firstNames)];
                $lastName = $lastNames[array_rand($lastNames)];
                $parentName = $parentNames[array_rand($parentNames)];
                $phone = $phones[array_rand($phones)];

                Student::create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'student_id' => sprintf('SCH-%06d', $counter++),
                    'school_class_id' => $class->id,
                    'gender' => rand(0, 1) ? 'male' : 'female',
                    'date_of_birth' => now()->subYears(rand(6, 14))->toDateString(),
                    'parent_name' => $parentName,
                    'parent_phone' => $phone,
                    'address' => 'Accra, Ghana',
                    'admission_date' => now()->subMonths(rand(6, 24))->toDateString(),
                ]);
            }
        }
    }
}
