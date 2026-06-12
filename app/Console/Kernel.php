<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run term activation check daily at midnight
        $schedule->command('terms:activate-next')->dailyAt('00:00');

        // Reconcile feeding fees every Monday at 8 AM (for previous week's attendance)
        $schedule->command('feeding-fees:reconcile')->weeklyOn(1, '08:00'); // 1 = Monday
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
