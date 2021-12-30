<?php

use App\Middleware\Session;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

function config($key, $default = null)
{
    global $configs;
    $current = $configs;
    $nested = explode('.', $key);
    foreach ($nested as $k) {
        if (!(is_array($current) && isset($current[$k]))) return $default;
        $current = $current[$k];
    }
    return $current;
}

function asset($name)
{
    if ($f = config('manifest')) {
        $data = json_decode(file_get_contents($f), true);
        if (is_array($data) && isset($data[$name])) {
            return config('app_prefix') . $data[$name];
        }
    }
    return $name;
}

function jsonResponse(ResponseInterface $response, $data = [], $pretty = false): ResponseInterface
{
    $response->getBody()->write(json_encode($data, $pretty ? JSON_PRETTY_PRINT : 0));
    return $response->withHeader('Content-Type', 'application/json');
}

function back(ServerRequestInterface $request, array $flash = []): ResponseInterface
{
    $params = $request->getServerParams();
    return redirect($params['HTTP_REFERER'] ?? config('app_prefix', '/'), 303, $flash);
}

function redirect($location, $code = 302, array $flash = []): ResponseInterface
{
    $flash && Session::setFlash($flash);
    $response = new Response($code);
    return $response->withHeader('Location', $location)->withStatus($code);
}

function intended(array $flash = [], $default = null): ResponseInterface
{
    $def = $default ?: (config('app_prefix', '/') ?: '/');
    return redirect(Session::get('intended_uri', $def), 303, $flash);
}
