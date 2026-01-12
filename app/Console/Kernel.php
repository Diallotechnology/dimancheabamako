<?php

declare(strict_types=1);

namespace App\Console;

use App\Models\PendingRegistration;
use App\Models\Promotion;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

final class Kernel extends ConsoleKernel
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
        // delete paiement expire chaque 5mn
        $schedule->command('app:delete-order-payment-expire')->everyFiveMinutes()->withoutOverlapping()->onOneServer()->runInBackground();
        // delete paiement expire chaque 3mn
        $schedule->command('app:delete-pay-by-link')->everyTwoMinutes()->withoutOverlapping()->onOneServer()->runInBackground();

        $schedule->call(function () {
            PendingRegistration::where('expires_at', '<', now())->delete();
        })->hourly();

        $schedule->call(function () {
            Promotion::where('fin', '<', now())
                ->where('etat', 'En cours')
                ->update(['etat' => 'Expiré']);
        })->dailyAt('00:00');
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
