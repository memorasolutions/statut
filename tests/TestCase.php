<?php

declare(strict_types=1);

namespace Memora\Statut\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [\Memora\Statut\StatutServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('cache.default', 'array');
        $app['config']->set('app.locale', 'fr');
        $app['config']->set('statut.providers.robotalp.api_key', 'test');
        $app['config']->set('statut.providers.robotalp.workspace_id', 999);
        $app['config']->set('statut.providers.robotalp.base_url', 'https://api.robotalp.com');
        $app['config']->set('statut.brand.name', 'Test');
        $app['config']->set('statut.brand.url', 'https://test.local');
        $app['config']->set('statut.layout', 'statut::tests.base');
        $app['config']->set('statut.section', 'content');
    }
}
