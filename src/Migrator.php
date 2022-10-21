<?php

declare(strict_types=1);

namespace DbMigrator;

class Migrator
{
    private string $migrationsTable;
    private \wpdb $wpdb;
    private ?int $batch = null;
    private bool $logEnabled = false;

    public function __construct()
    {
        global $wpdb;

        $this->wpdb = $wpdb;

        $this->migrationsTable = $wpdb->prefix . 'migrations';

        if(defined('WP_CLI') && WP_CLI) {
            $this->logEnabled = true;
        }
    }

    public function migrate()
    {
        $this->createMigrationsTable();

        $migrationDirs = [];

        $migrationDirs = apply_filters('dbmigrator_migrations_dirs', $migrationDirs);

        $this->batch = $this->getNextBatch();

        $pendingMigrations = $this->getPendingMigrations($migrationDirs);

        if ($this->logEnabled && !count($pendingMigrations)) {
            \WP_CLI::log("Nothing to migrate.");
        }

        foreach ($pendingMigrations as $migrationName => $migrationFile) {
            if($this->logEnabled) {
                \WP_CLI::log("Migrating: {$migrationName}");
            }

            $class = require $migrationFile;

            if (!is_subclass_of($class, Migration::class)) {
                throw new \RuntimeException("Migration {$migrationName}.php have to extend the base Migration class!");
            }

            $this->runUp($class, $migrationName);

            if($this->logEnabled) {
                \WP_CLI::log("Migrated: {$migrationName}" . PHP_EOL);
            }
        }
    }

    private function createMigrationsTable()
    {
        $charset_collate = $this->wpdb->get_charset_collate();

        $sql = "CREATE TABLE $this->migrationsTable (
            id int(11) NOT NULL auto_increment,
            migration varchar(60) NOT NULL,
            batch int(11) NOT NULL,
            UNIQUE KEY id (id)
        ) $charset_collate;";


        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private function getNextBatch(): int
    {
        $result = $this->wpdb->get_var(
            "SELECT MAX(batch) from {$this->migrationsTable}"
        );

        $lastBatch = (int)$result;

        return $lastBatch + 1;
    }

    private function runUp($class, string $migrationName)
    {
        $class->up();
        $this->storeMigration($migrationName);
    }

    private function getPendingMigrations(array $migrationDirs)
    {
        $fileMigrations = $this->getMigrationFilesNames($migrationDirs);

        $dbMigrations = array_map(function ($item) {
            return $item->migration;
        }, $this->getAllMigrations());

        return array_filter($fileMigrations, function ($migrationName) use ($dbMigrations) {
            return !in_array($migrationName, $dbMigrations);
        }, ARRAY_FILTER_USE_KEY);
    }

    public function storeMigration(string $migrationName)
    {
        $this->wpdb->insert($this->migrationsTable, [
            'migration' => $migrationName,
            'batch' => $this->batch
        ]);
    }

    private function getMigrationFilesNames(array $migrationDirs)
    {
        $migrations = [];


        foreach ($migrationDirs as $dir) {
            $absPaths = glob("$dir/*.php");

            $currentDirMigrations = [];

            foreach ($absPaths as $path) {
                $migrationName = str_replace('.php', '', basename($path));

                $currentDirMigrations[$migrationName] = $path;
            }

            $migrations = array_merge($migrations, $currentDirMigrations);
            $currentDirMigrations = [];
        }

        return $migrations;
    }

    private function getAllMigrations()
    {
        $results = $this->wpdb->get_results(
            "SELECT * FROM {$this->migrationsTable}"
        );

        return $results;
    }
}
