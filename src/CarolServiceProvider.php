<?php

namespace eirworks\carol;

use eirworks\carol\Console\Commands\Crawl;
use eirworks\carol\Console\Commands\Crawler\CrawlRSS;
use eirworks\carol\Console\Commands\CrawlYoutube;
use eirworks\carol\Lib\RSSParser;
use eirworks\carol\Support\Sources;
use Illuminate\Support\ServiceProvider;

class CarolServiceProvider extends ServiceProvider
{
    /**
     * Register the package services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/carol.php', 'carol');

        $this->app->singleton(RSSParser::class);

        $this->app->singleton(Sources::class, function ($app) {
            return new Sources(
                (array) $app['config']->get('carol.sources', []),
                \function_exists('resource_path') ? resource_path('search') : null,
            );
        });
    }

    /**
     * Bootstrap the package services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            Crawl::class,
            CrawlRSS::class,
            CrawlYoutube::class,
        ]);

        $this->publishes([
            __DIR__ . '/../config/carol.php' => $this->app->configPath('carol.php'),
        ], 'carol-config');

        $this->publishes([
            __DIR__ . '/../resources/search' => $this->app->resourcePath('search'),
        ], 'carol-resources');

        $this->publishes([
            __DIR__ . '/../database/migrations' => $this->app->databasePath('migrations'),
        ], 'carol-migrations');
    }
}
