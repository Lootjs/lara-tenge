<?php
namespace Loot\Tenge\Test;

use Orchestra\Testbench\TestCase;
use Loot\Tenge\ {
    TengeFacade,
    ServiceProvider
};

abstract class BaseTest extends TestCase
{
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set(
            'database.connections.testbench', [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ]
        );
    }
    /**
     * Setup the test environment.
     */
    public function setUp(): void
    {
        parent::setUp();
        //$this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }
    /**
     * Get package aliases.
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'Tenge' => TengeFacade::class,
        ];
    }
}
