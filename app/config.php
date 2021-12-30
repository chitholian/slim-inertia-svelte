<?php

return [
    'db' => [
        'username' => 'atik',
        'password' => '0000',
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'the_db',
    ],
    'app_env' => 'dev', // can be "prod" or "dev".
    'app_prefix' => '/',
    'manifest' => __DIR__ . '/../public/dist/manifest.json',
    'templates' => __DIR__ . '/../template',
    'cache_path' => __DIR__ . '/../cache',
];
