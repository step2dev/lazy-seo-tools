<?php

namespace Step2dev\LazySeoTools\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Step2dev\LazySeoTools\LazySeoServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        $providers = [LazySeoServiceProvider::class];

        if (class_exists(\Livewire\LivewireServiceProvider::class)) {
            array_unshift($providers, \Livewire\LivewireServiceProvider::class);
        }

        return $providers;
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);
        $app['config']->set('lazy-seo.admin.enabled', true);

        $app['config']->set('auth.guards.sanctum', [
                    'driver' => 'session',
                    'provider' => null,
                ]);


        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        $app['config']->set('app.url', 'https://example.com');

$app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
