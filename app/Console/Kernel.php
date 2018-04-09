<?php

namespace App\Console;

use App\Console\Commands\CheckUsersStockQuote;
use App\Console\Commands\CrawlStockCodeAndName;
use App\Console\Commands\CrawlStockQuotes;
use App\Console\Commands\PrepareUserWarningConfigsQueue;
use App\Console\Commands\SetNumberOfWarnings;
use App\Stock;
use App\User;
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
        CrawlStockQuotes::class,
        CrawlStockCodeAndName::class,
        PrepareUserWarningConfigsQueue::class,
        CheckUsersStockQuote::class,
        SetNumberOfWarnings::class,
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        $schedule->command('crawl:stockQuotes')->everyMinute();
        $schedule->command('prepare:userWarningConfigsQueue')->everyMinute();
        $schedule->command('set:numberOfWarnings')->daily();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
