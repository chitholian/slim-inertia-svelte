#!/usr/bin/env php
<?php

use App\Database\DB;
use App\Database\Migrator;
use GetOpt\ArgumentException;
use GetOpt\GetOpt;

require __DIR__ . '/init.php';

$getOpts = new GetOpt([
    ['c', 'create', GetOpt::MULTIPLE_ARGUMENT, 'Create a migration file.'],
    ['m', 'migrate', GetOpt::NO_ARGUMENT, 'Migrate new migrations.'],
    ['r', 'rollback', GetOpt::NO_ARGUMENT, 'Rollback latest migrations.'],
    ['t', 'truncate', GetOpt::NO_ARGUMENT, 'Rollback all migrations.'],
]);
try {
    $getOpts->process($argv);
} catch (ArgumentException $exception) {
    echo 'Error: ' . $exception->getMessage() . "\n";
    echo $getOpts->getHelpText();
    exit(1);
}
$opts = $getOpts->getOptions();

$migrator = new Migrator();
$acted = false;

if (isset($opts['t'])) {
    $migrator->setDB(DB::getInstance());
    // Undo migrations.
    $migrator->truncateDB();
    $acted = true;
}
if (isset($opts['r'])) {
    $migrator->setDB(DB::getInstance());
    // Undo migrations.
    $migrator->rollback();
    $acted = true;
}
if (isset($opts['m'])) {
    $migrator->setDB(DB::getInstance());
    // Apply migrations.
    $migrator->migrate();
    $acted = true;
}
if (!empty($opts['c'])) {
    foreach ($opts['c'] as $name) {
        $migrator->generateMigrationClass($name);
    }
    $acted = true;
}

if (!$acted) {
    echo $getOpts->getHelpText();
    exit(1);
}
