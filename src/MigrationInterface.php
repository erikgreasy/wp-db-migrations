<?php

namespace DbMigrator;

interface MigrationInterface
{
    public function run(): void;
}
