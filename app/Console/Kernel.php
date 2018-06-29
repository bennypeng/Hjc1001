<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
//use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\AutoBirth::class,
        \App\Console\Commands\AutoMatch::class,
        \App\Console\Commands\CleanPet::class,
        \App\Console\Commands\AutoCoin::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('match:generate')->daily();              //  开比赛
        $schedule->command('pet:birth')->everyFifteenMinutes();     //  出生宠物
        $schedule->command('pet:clear')->hourly();                  //  宠物出生
        $schedule->command('tx:send')->everyMinute();               //  下发积分
        //$schedule->call(function () {
        //    DB::table('recent_users')->delete();
        //})->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
