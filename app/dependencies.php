<?php

use App\Database\DB;
use App\Repo\ExtraRepo;
use App\Repo\UserRepo;
use Psr\Container\ContainerInterface;

return [
    DB::class => function () {
        return DB::getInstance();
    },
    UserRepo::class => function (ContainerInterface $c) {
        return new UserRepo($c->get(DB::class));
    },
    ExtraRepo::class => function (ContainerInterface $c) {
        return new ExtraRepo($c->get(DB::class));
    },
];
