<?php

namespace App\Console\Commands;

use App\Models\Term;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ActivateNextTerm extends Command
{
    protected $signature = 'terms:activate-next';

    protected $description = 'Deactivate expired terms and activate the next available term';

    public function handle()
    {
        // Find all active terms that have passed their end date
        $expiredTerms = Term::where('is_active', true)
            ->where('end_date', '<', today())
            ->get();

        foreach ($expiredTerms as $expiredTerm) {
            // Deactivate the expired term
            $expiredTerm->update(['is_active' => false]);
            Log::info("Term deactivated: {$expiredTerm->name} (ID: {$expiredTerm->id})");

            // Find the next available term (earliest start_date that hasn't started yet or just starting)
            $nextTerm = Term::where('id', '!=', $expiredTerm->id)
                ->where('is_active', false)
                ->where('start_date', '<=', today())
                ->where('end_date', '>=', today())
                ->orderBy('start_date', 'asc')
                ->first();

            // If no currently running term, find the next upcoming term
            if (! $nextTerm) {
                $nextTerm = Term::where('id', '!=', $expiredTerm->id)
                    ->where('is_active', false)
                    ->where('start_date', '>', today())
                    ->orderBy('start_date', 'asc')
                    ->first();
            }

            // Activate the next term if found
            if ($nextTerm) {
                $nextTerm->update(['is_active' => true]);
                Log::info("Term activated: {$nextTerm->name} (ID: {$nextTerm->id})");
                $this->info("Activated: {$nextTerm->name}");
            } else {
                Log::warning("No next term available after deactivating: {$expiredTerm->name}");
                $this->warn("No next term available after deactivating: {$expiredTerm->name}");
            }
        }

        if ($expiredTerms->isEmpty()) {
            $this->info('No expired terms found.');
        }
    }
}
