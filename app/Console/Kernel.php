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

        // verification paiement chaque munite
        $schedule->command('app:check-payment')->everyMinute()->withoutOverlapping()->onOneServer()->runInBackground();
        // verification paiement chaque 3 munites
        $schedule->command('app:check-pay-by-link')->everyTwoMinutes()->withoutOverlapping()->onOneServer()->runInBackground();

        // verification des jobs
        $schedule->command('queue:work --stop-when-empty')->everyMinute()->withoutOverlapping()->onOneServer()->runInBackground();

        $schedule->command('queue:retry')->everyTwoMinutes()->withoutOverlapping()->onOneServer()->runInBackground();
        // delete paiement expire chaque 3mn
        $schedule->command('app:delete-order-payment-expire')->everyTwoMinutes()->withoutOverlapping()->onOneServer()->runInBackground();
        // delete paiement expire chaque 3mn
        $schedule->command('app:delete-pay-by-link')->everyTwoMinutes()->withoutOverlapping()->onOneServer()->runInBackground();

        //verification user email not verified
        $schedule->command('app:delete-user-not-verify')->everyThirtyMinutes()->withoutOverlapping()->onOneServer()->runInBackground();
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
