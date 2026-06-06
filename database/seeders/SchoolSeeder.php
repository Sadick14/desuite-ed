<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        School::create([
            'name' => 'Bright Future Academy',
            'email' => 'info@brightfuture.edu.gh',
            'phone' => '+233 24 123 4567',
            'address' => '123 Education Street, Accra, Ghana',
        ]);
    }
}
