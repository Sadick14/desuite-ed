<?php

namespace App\Console\Commands;

use App\Models\StudentEnrollment;
use App\Models\Term;
use App\Services\FeedingFeeService;
use Illuminate\Console\Command;

class ReconcileFeedingFees extends Command
{
    protected $signature = 'feeding-fees:reconcile {--week=}';

    protected $description = 'Auto-reconcile feeding fees for current week based on attendance';

    public function handle()
    {
        $activeTerm = Term::where('is_active', true)->first();

        if (! $activeTerm) {
            $this->info('No active term found.');

            return 0;
        }

        $weekNumber = $this->option('week') ?? FeedingFeeService::getCurrentWeekNumber($activeTerm);

        if ($weekNumber < 1) {
            $this->info('Term has not started yet.');

            return 0;
        }

        $this->info("Reconciling feeding fees for {$activeTerm->name} - Week {$weekNumber}");

        // Get all students enrolled in this term
        $enrollments = StudentEnrollment::where('academic_year_id', $activeTerm->academic_year_id)
            ->where('status', 'active')
            ->with('student')
            ->get();

        $reconciled = 0;
        $failed = 0;

        foreach ($enrollments as $enrollment) {
            try {
                FeedingFeeService::reconcileWeeklyFee($enrollment->student, $activeTerm, $weekNumber);
                $reconciled++;
            } catch (\Exception $e) {
                $this->error("Failed to reconcile for student {$enrollment->student->id}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->info("Reconciliation complete: {$reconciled} succeeded, {$failed} failed");

        return 0;
    }
}
