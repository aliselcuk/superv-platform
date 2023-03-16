<?php

namespace SuperV\Platform\Domains\Database\Migrations;

use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Events\Dispatcher;
use SuperV\Platform\Domains\Database\Migrations\Console\MigrateCommand;
use SuperV\Platform\Domains\Database\Migrations\Console\MigrateMakeCommand;
use SuperV\Platform\Domains\Database\Migrations\Console\RefreshCommand;
use SuperV\Platform\Domains\Database\Migrations\Console\ResetCommand;
use SuperV\Platform\Domains\Database\Migrations\Console\RollbackCommand;

class MigrationServiceProvider extends \Illuminate\Database\MigrationServiceProvider
{
    public function register()
    {
        $this->registerRepository();

        $this->registerMigrator();

        $this->registerCreator();

        $this->extendMigrationRepository();

        // @laravel-6.0
        if (method_exists($this, 'registerCommands')) {
            $this->registerCommands($this->commands);
        }

        $this->extendConsoleCommands();
    }

    protected function registerRepository()
    {
        $this->app->singleton('migration.repository', function ($app) {
            $table = $app['config']['database.migrations'];

            return new DatabaseMigrationRepository($app['db'], $table);
        });
    }

    protected function registerMigrator()
    {
        $this->app->singleton('migrator', function ($app) {
            $repository = $app['migration.repository'];

            return new Migrator($repository, $app['db'], $app['files'], $app['events']);
        });
    }

    protected function registerCreator()
    {
        $this->app->singleton('migration.creator', function ($app) {
            return new MigrationCreator($app['files'], \Platform::fullPath('resources/stubs'));
        });
    }

    protected function extendConsoleCommands(): void
    {
        $this->app->extend(
            'command.migrate.make',
            function ($command, $app) {
                return new MigrateMakeCommand($app['migration.creator'], $app['composer']);
            }
        );

        $this->app->extend(
            'command.migrate',
            function ($command, $app) {
                return new MigrateCommand($app['migrator'], $app[Dispatcher::class]);
            }
        );

        $this->app->extend(
            'command.migrate.rollback',
            function ($command, $app) {
                return new RollbackCommand($app['migrator']);
            }
        );

        $this->app->extend(
            'command.migrate.reset',
            function ($command, $app) {
                return new ResetCommand($app['migrator']);
            }
        );

        $this->app->extend(
            'command.migrate.refresh',
            function () {
                return new RefreshCommand();
            }
        );
    }

    protected function extendMigrationRepository(): void
    {
        $this->app->bind(MigrationRepositoryInterface::class, DatabaseMigrationRepository::class);
    }
}