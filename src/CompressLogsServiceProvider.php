<?php

namespace Codewisdoms\CompressLogs;

use Codewisdoms\CompressLogs\Commands\LogCompressCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class CompressLogsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => $this->app->configPath('log-compress.php'),
            ], 'config');

            $this->commands([
                LogCompressCommand::class,
            ]);

            $this->registerSchedule();
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'log-compress');
    }

    private function registerSchedule()
    {
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('logs:compress')->daily();
        });
    }
}
