<?php

use DbMigrator\DbMigratorPlugin;

/**
 * Plugin name: DB migrator
 * Description: Run Laravel-like database migrations in WordPress with WP_CLI commands
 * Version: 0.1.0
 * Author: Erik Masny
 * Author URI: https://github.com/erikgreasy
 */

if(defined('WP_CLI') && WP_CLI) {
    (new DbMigratorPlugin)->run();
}
