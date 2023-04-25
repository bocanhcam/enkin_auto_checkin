<?php

namespace App\Console;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $time = env("ENKIN_LOG_TIME", "08:25");

        if (env("ENKIN_RANDOM_TIME")){
            $start_time = Carbon::parse('08:10:00');
            $end_time = Carbon::parse('08:29:00');

            $random_seconds = mt_rand(0, $end_time->diffInSeconds($start_time));

            $time = $start_time->copy()->addSeconds($random_seconds);
        }

        $schedule->command('enkin')->weekdays()->at($time);
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
