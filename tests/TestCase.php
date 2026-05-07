<?php

namespace Step2dev\LazySeoTools\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Step2dev\LazySeoTools\LazySeoServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LazySeoServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('lazy-seo.routes.api', true);
        $app['config']->set('lazy-seo.routes.web', true);
        $app['config']->set('lazy-seo.routes.api_middleware', ['api']);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
