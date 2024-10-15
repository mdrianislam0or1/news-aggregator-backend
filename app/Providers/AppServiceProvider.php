<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\Artisan;

class AppServiceProvider extends ServiceProvider
{

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $commandName = 'serve';
            $command = trim($_SERVER['argv'][1] ?? '');
            if ($command === $commandName) {
                Artisan::call('articles:fetch');
            }
        }
    }
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
}
