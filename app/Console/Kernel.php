<?php

namespace App\Console;

use App\Models\BotRequest;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected array $offDates = [
        '2023-08-10',
        '2023-08-11',
        '2023-09-01',
        '2023-09-04',
    ];
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $currentDate = Carbon::now();

        $botRequest = BotRequest::query()->whereDate('date', $currentDate)->orderBy('id', 'desc')->first();

        // Working days
        if (!in_array($currentDate->toDateString(), $this->offDates)){
            $randomMinute = rand(20, 28);

            $time = Carbon::createFromTime(8, $randomMinute);

            if (!empty($botRequest)){
                if ($botRequest->type == BotRequest::TYPE_OFF || $botRequest->type == BotRequest::TYPE_MANUAL){
                    return;
                }

                if ($botRequest->type == BotRequest::TYPE_LATE_30M){
                    $time->addMinutes(30);
                }

                if ($botRequest->type == BotRequest::TYPE_LATE_1h){
                    $time->addHour();
                }
            }

            $schedule->command('enkin:work')->weekdays()->at($time->format('H:i'));
            $schedule->command('enkin:leave')->weekdays()->at('17:30');
        }
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
