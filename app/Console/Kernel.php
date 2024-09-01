<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define o agendamento de comandos da aplicação.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('emails:send-daily')
                 ->dailyAt('08:00')
                 ->withoutOverlapping()
                 ->onFailure(function () {
                     \Log::error('Falha ao enviar e-mails diários.');
                 })
                 ->onSuccess(function () {
                     \Log::info('E-mails diários enviados com sucesso.');
                 });

        $schedule->command('logs:clear')
                 ->weeklyOn(0, '03:00')
                 ->runInBackground();

        $schedule->command('backup:run')
                 ->dailyAt('02:00')
                 ->onOneServer()
                 ->runInBackground();

        $schedule->command('currency:update')
                 ->hourly()
                 ->onOneServer()
                 ->runInBackground();
    }

    /**
     * Registra os comandos da aplicação.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
