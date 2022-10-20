<?php

namespace DbMigrator;

abstract class Migration
{
    protected \wpdb $wpdb;

    public function __construct()
    {
        global $wpdb;

        $this->wpdb = $wpdb;
    }

    public function getPrefixedTable(string $tableName): string
    {
        return $this->wpdb->prefix . $tableName;
    }
}
