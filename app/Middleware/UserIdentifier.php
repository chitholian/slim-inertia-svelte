<?php

namespace App\Middleware;

use App\Repo\ExtraRepo;
use App\Repo\UserRepo;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UserIdentifier extends BaseMiddleware
{
    private static ?array $user = null;

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (Session::isActive()) {
            $uid = Session::get('user_id');
            /** @var UserRepo $repo */
            $repo = $this->container->get(UserRepo::class);
            if (static::$user = $repo->findById($uid)) {
                // Track this device and user.
                $did = static::getDeviceID();
                /** @var ExtraRepo $repo */
                $repo = $this->container->get(ExtraRepo::class);
                $params = $request->getServerParams();
                $device = [
                    'did' => $did,
                    'ip' => $params['REMOTE_ADDR'] ?? 'Unknown',
                    'port' => $params['REMOTE_PORT'] ?? 0,
                    'user_agent' => $params['HTTP_USER_AGENT'] ?? null,
                ];
                $repo->trackDevice($device, static::$user);
            }
        } else {
            static::$user = null;
        }
        return $handler->handle($request);
    }

    public static function getUser(): ?array
    {
        return static::$user;
    }

    public static function getDeviceID(): string
    {
        $did = Session::getCookie('did');
        if (!$did) {
            $did = base64_encode(random_bytes(8) . microtime());
            Session::setCookie('did', $did, ['expires' => time() + 86400 * 365 * 20]);
        }
        return $did;
    }
}
