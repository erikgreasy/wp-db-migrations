<?php

namespace DbMigrator;

use RuntimeException;
use DbMigrator\Migrator;
use DbMigrator\Migration;

class DbMigratorPlugin
{
    public function run()
    {
        \WP_CLI::add_command('migrator migrate', function () {
            \WP_CLI::log('Running migrations...' . PHP_EOL);
            (new Migrator())->migrate();
        });

        \WP_CLI::add_command('migrator make:migration', function($args, $assocArgs) {
            if(!count($args) || !isset($args[0])) {
                throw new RuntimeException('You must specify the migration name!');
            }

            $migrationName = $args[0];

            $migrationStub = file_get_contents(__DIR__ . '/../stubs/migration.stub');

            file_put_contents($migrationName . '.php', $migrationStub);
        });
    }
}
