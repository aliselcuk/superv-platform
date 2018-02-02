<?php

namespace Tests\SuperV\Platform\Packs\Database\Migrations;

use Illuminate\Database\Migrations\Migrator as BaseMigrator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Packs\Database\Migrations\Migrator;
use Tests\SuperV\Platform\BaseTestCase;

class MigratorTest extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    function extends_framework_migrator()
    {
        $migrator = new Migrator(app('migration.repository'), app('db'), app('files'));

        $this->assertInstanceOf(BaseMigrator::class, $migrator);
    }

    /**
     * @test
     */
    function save_migrations_scope_if_it_has_one()
    {
        $this->app['migrator']->run(__DIR__.'/migrations');

        $this->assertDatabaseHas('migrations', ['scope' => 'foo']);
        $this->assertDatabaseHas('migrations', ['scope' => 'bar']);
    }

    /**
     * @test
     */
    function rollback_migrations_by_scope()
    {
        $this->app['migrator']->run(__DIR__.'/migrations');
        $this->assertDatabaseHas('migrations', ['scope' => 'bar']);

        $this->app['migrator']->setScope('bar')->rollback(__DIR__.'/migrations');
        $this->assertDatabaseMissing('migrations', ['scope' => 'bar']);
        $this->assertDatabaseHas('migrations', ['scope' => 'foo']);
    }

    /**
     * @test
     */
    function run_migrations_by_scope()
    {
        $this->app['migrator']->setScope('bar')->run(__DIR__.'/migrations');

        $this->assertDatabaseMissing('migrations', ['scope' => 'foo']);
        $this->assertDatabaseHas('migrations', ['scope' => 'bar']);
    }
}