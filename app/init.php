<?php

const APP_VERSION = '1.0.0';

require __DIR__ . '/../vendor/autoload.php';
$configs = (require __DIR__ . '/config.php');
require __DIR__ . '/helpers.php';

$container = new \App\Container\Container();
$container->configureFromFile(__DIR__ . '/dependencies.php');

\Slim\Factory\AppFactory::setContainer($container);
$app = \Slim\Factory\AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
require __DIR__ . '/routes.php';
if (config('app_env') === 'prod') {
    $routeCollector = $app->getRouteCollector();
    $routeCollector->setCacheFile(config('cache_path', sys_get_temp_dir()) . '/routes.cache.php');
}

$app->addErrorMiddleware(config('app_env') === 'dev', true, true);
$app->run();
