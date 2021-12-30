<?php

namespace App\Controller;

use App\Middleware\Inertia;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class IndexController extends BaseController
{
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = [];
        return Inertia::render($request, $response, 'HomePage', $data);
    }
}
