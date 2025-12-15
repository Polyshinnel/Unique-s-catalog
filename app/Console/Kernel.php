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
        $schedule->command('advertisements:import')->hourly();
        // Быстрое обновление товаров каждые 5 минут
        $schedule->command('advertisements:quick-update')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground();

        // Полное обновление товаров в полночь
        $schedule->command('advertisements:full-update')
            ->dailyAt('00:00')
            ->withoutOverlapping()
            ->runInBackground();
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
