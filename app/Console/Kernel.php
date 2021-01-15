<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\Workflow\NextInfo::class,
        Commands\Workflow\Vacation::class,
        Commands\Workflow\MonthlyInfo::class,
        Commands\Message::class,
        Commands\Report\Info::class,
        Commands\Report\Reminder::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('workflow:next-info')->dailyAt('20:00');
        $schedule->command('workflow:vacation')->quarterly()->at('12:00');
        $schedule->command('workflow:monthly-info')->monthly()->at('12:00')->when(function () {
            return date('d') > date('d', strtotime(date('Y-m-d') . '+1day')); // last day in month
        });
        $schedule->command('report:info')->weekdays()->at('22:00');
        $schedule->command('report:reminder')->weekdays()->at('18:00');
    }
}
