<?php

namespace App\Providers;

use App\DuskDrivers\Chrome;
use Illuminate\Console\Command;
use Laravel\Dusk\Browser;
use NunoMaduro\LaravelConsoleDusk\LaravelConsoleDuskServiceProvider;
use NunoMaduro\LaravelConsoleDusk\Manager;

class DuskServiceProvider extends LaravelConsoleDuskServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                base_path('vendor/nunomaduro/laravel-console-dusk/config/laravel-console-dusk.php') => config_path('laravel-console-dusk.php'),
            ], 'config');

            $manager = new Manager(new Chrome());

            Browser::$baseUrl = config('app.url');
            Browser::$storeScreenshotsAt = $this->getPath(config('laravel-console-dusk.paths.screenshots'));
            Browser::$storeConsoleLogAt = $this->getPath(config('laravel-console-dusk.paths.log'));
            Browser::$storeSourceAt = $this->getPath(config('laravel-console-dusk.paths.source'));

            Command::macro('browse', function ($callback) use ($manager) {
                $manager->browse($this, $callback);
            });
        }
    }
}
