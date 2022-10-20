<h1 align=center>WordPress database migrations</h1>
<p align=center><i>Databse migrations for WordPress inspired by Laravel framework.</i></p>

# Installation
There are few ways you can install WP DB migrations:

## 1. Install as a mu-plugin.
Download the latest release ZIP and extract it into your mu-plugins. You will need some type of must-use plugins autoloader in order for this to work, for example [Bedrock Autoloader](https://github.com/roots/bedrock-autoloader).

## 2. Install as a regular plugin
Download the latest release ZIP and extract it into your plugins, or install via wp-admin plugins section.

# Getting started
## Registering your migrations folders
When running the migrations with WP CLI command, the plugin scans all folders that are registered as "migrations folders". Migration folder is basically a folder in your plugin/theme, where your migrations are stored.

The plugin supports registering multiple migrations folders, so you can have separate migration folder for mulitple plugins.

To register a new migration folder, use the following WP filter:
```PHP
add_filter('dbmigrator_migrations_dirs', function($migrationDirs) {
    $migrationDirs[] = __DIR__ . '/my_plugin_migrations';

    return $migrationDirs;
});

```

## Creating your first migration file
Migration file is just a PHP file, which follows specific structure, so the plugin can scan this file and run.

### Migration structure
Simple example migration may look like this:

```PHP
<?php
// 001_reviewplugin_create_test_table.php

use DbMigrator\Migration;

return new class extends Migration
{
    public function up()
    {
        $tableName = $this->getPrefixedTable('test');
        $charset_collate = $this->wpdb->get_charset_collate();

        $sql = "CREATE TABLE $tableName (
            id int(11) NOT NULL auto_increment,
            name varchar(60) NOT NULL,
            UNIQUE KEY id (id)
        ) $charset_collate;";

        $this->wpdb->query($sql);
    }
};
```

Few important points to take from example above are:
- we use PHP **anonymous classes that extends the base Migration class**. This base class provides few useful methods and properties for working with wpdb.
- the **up() method is required** and contains all your migration logic
- naming convention for the migration file is as follows:
  - to prefix your migration with index (eg. 001)
  - use some kind of identifier (eg. reviewplugin)
  - the rest of the file name should describe what the migration does
  - use snake_case for better readability

### Creating migration files
You can create the migrations files manually, or you can generate them with WP CLI command:
```
wp migrator make:migrations 001_your_migration_name
```
which will create an empty migration with specified name in the location, from where the command was run.

## Running your migrations
Now you are ready to run your migrations. To run the migrations, use the WP CLI command:
```
wp migrator migrate
```
