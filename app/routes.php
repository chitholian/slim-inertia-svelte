<?php

global $app;

use Slim\Routing\RouteCollectorProxy;

$app->group('/api', function (RouteCollectorProxy $route) {
    $route->get('/my-ip', [\App\Controller\ExtraController::class, 'myIP']);
});

$app->group('', function (RouteCollectorProxy $route) {
    $route->group('', function (RouteCollectorProxy $route) {
        $route->get('/', [\App\Controller\IndexController::class, 'index']);
        $route->get('/logout', [\App\Controller\UserController::class, 'logout']);

        $route->get('/devices', [\App\Controller\DeviceController::class, 'report']);
        $route->patch('/devices', [\App\Controller\DeviceController::class, 'takeAction']);

        $route->get('/reports/logins', [\App\Controller\UserController::class, 'loginReport']);
        $route->patch('/reports/logins', [\App\Controller\UserController::class, 'modifyLoginReport']);

        $route->get('/profile/change-password', [\App\Controller\UserController::class, 'changePassForm']);
        $route->patch('/profile/change-password', [\App\Controller\UserController::class, 'changePass']);
    })->add(\App\Middleware\AuthGuard::class);

    $route->get('/login', [\App\Controller\UserController::class, 'loginForm'])->setName('login');
    $route->post('/login', [\App\Controller\UserController::class, 'login']);
    $route->get('/captcha', [\App\Controller\ExtraController::class, 'captcha'])->setName('captcha');
})->add(\App\Middleware\UserIdentifier::class)->add(\App\Middleware\Session::class)->add(\App\Middleware\Inertia::class);
