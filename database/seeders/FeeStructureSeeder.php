<?php

namespace Database\Seeders;

use App\Models\FeeStructure;
use App\Models\Term;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FeeStructureSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $terms = Term::all();

        // Fee structures per level (matching your level values)
        $feeStructures = [
            'nursery' => [
                ['fee_type' => 'school_fees', 'amount' => 250],
                ['fee_type' => 'feeding_fees', 'amount' => 5],
                ['fee_type' => 'registration_fees', 'amount' => 40],
                ['fee_type' => 'others', 'amount' => 80],
            ],
            'kindergarten' => [
                ['fee_type' => 'school_fees', 'amount' => 250],
                ['fee_type' => 'feeding_fees', 'amount' => 5],
                ['fee_type' => 'registration_fees', 'amount' => 40],
                ['fee_type' => 'others', 'amount' => 80],
            ],
            'lower_primary' => [
                ['fee_type' => 'school_fees', 'amount' => 300],
                ['fee_type' => 'feeding_fees', 'amount' => 5],
                ['fee_type' => 'registration_fees', 'amount' => 50],
                ['fee_type' => 'others', 'amount' => 100],
            ],
            'upper_primary' => [
                ['fee_type' => 'school_fees', 'amount' => 350],
                ['fee_type' => 'feeding_fees', 'amount' => 6],
                ['fee_type' => 'registration_fees', 'amount' => 60],
                ['fee_type' => 'others', 'amount' => 120],
            ],
            'jhs' => [
                ['fee_type' => 'school_fees', 'amount' => 400],
                ['fee_type' => 'feeding_fees', 'amount' => 8],
                ['fee_type' => 'registration_fees', 'amount' => 75],
                ['fee_type' => 'others', 'amount' => 150],
            ],
        ];

        // Create fee structures per term and level
        foreach ($terms as $term) {
            foreach ($feeStructures as $level => $fees) {
                foreach ($fees as $fee) {
                    FeeStructure::updateOrCreate(
                        [
                            'term_id' => $term->id,
                            'level' => $level,
                            'fee_type' => $fee['fee_type'],
                        ],
                        ['amount' => $fee['amount']]
                    );
                }
            }
        }
    }
}
