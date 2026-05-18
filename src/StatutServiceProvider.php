<?php

declare(strict_types=1);

namespace Memora\Statut;

use Illuminate\Support\ServiceProvider;
use Memora\Statut\Contracts\MonitoringProvider;

final class StatutServiceProvider extends ServiceProvider
{
    /**
     * Enregistre les liaisons du service.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/statut.php', 'statut');

        $this->app->singleton(MonitoringProvider::class, function ($app) {
            $provider = $app['config']->get('statut.provider', 'robotalp');

            if ($provider === 'robotalp' && class_exists(\Memora\Statut\Providers\RobotalpProvider::class)) {
                return new \Memora\Statut\Providers\RobotalpProvider(
                    (array) $app['config']->get('statut.providers.robotalp', [])
                );
            }

            throw new \RuntimeException("Monitoring provider [{$provider}] is not supported or not installed.");
        });

        $this->app->alias(MonitoringProvider::class, 'statut');
    }

    /**
     * Bootstrap les services du package.
     */
    public function boot(): void
    {
        if (! $this->app['config']->get('statut.enabled', true)) {
            return;
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/statut.php' => config_path('statut.php'),
            ], 'statut-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/statut'),
            ], 'statut-views');

            $this->publishes([
                __DIR__.'/../resources/lang' => lang_path('vendor/statut'),
            ], 'statut-lang');
        }

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'statut');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'statut');
    }
}
