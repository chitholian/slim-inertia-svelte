<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;

class AuthGuard implements MiddlewareInterface
{

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (UserIdentifier::getUser()) {
            Session::clear('intended');
            return $handler->handle($request);
        }
        Session::put('intended', (string)$request->getUri());
        $r = RouteContext::fromRequest($request);
        return redirect($r->getRouteParser()->urlFor('login'), 303, ['danger' => 'You must login first']);
    }
}
