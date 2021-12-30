<?php

namespace App\Controller;

use App\Database\DB;
use App\Middleware\Session;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class BaseController
{
    protected DB $db;
    protected int $page = 1, $dpp = 50, $offset = 0;
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->db = DB::getInstance();
    }

    protected function resolveQueryOffset(ServerRequestInterface $request): int
    {
        $params = $request->getQueryParams();
        $this->page = max(intval($params['page'] ?? 1), 1);
        if (!empty($params['dpp'])) {
            Session::put('dpp', intval($params['dpp']) ?: 50);
        }
        $this->dpp = Session::get('dpp', 50);
        return $this->offset = $this->dpp * ($this->page - 1);
    }
}
