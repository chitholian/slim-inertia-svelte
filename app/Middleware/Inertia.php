<?php

namespace App\Middleware;

use App\Model\InertiaPage;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Inertia implements MiddlewareInterface
{

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$request->hasHeader('X-Inertia')) {
            return $handler->handle($request);
        }
        if ($request->getMethod() === 'GET' && $request->hasHeader('X-Inertia-Version')) {
            $v = $request->getHeaderLine('X-Inertia-Version');
            if ($v !== static::getAssetVersion()) {
                return (new Response(409))->withHeader('X-Inertia-Location', $request->getUri()->getPath());
            }
        }

        return $handler->handle($request);
    }

    public static function render(ServerRequestInterface $request, ResponseInterface $response, string $component, array $data = []): ResponseInterface
    {
        $page = new InertiaPage($component, (string)$request->getUri(), static::getAssetVersion(), array_merge(static::getSharedData(), $data));
        if (!$request->hasHeader('X-Inertia')) {
            return static::renderHtml($response, $page);
        }
        $response = $response->withHeader('Vary', 'Accept')->withHeader('X-Inertia', "true");

        if ($request->hasHeader('X-Inertia-Partial-Data') &&
            $request->hasHeader('X-Inertia-Partial-Component')) {
            $comp = $request->getHeaderLine('X-Inertia-Partial-Component');
            if ($component === $comp) {
                $propList = explode(',', $request->getHeaderLine('X-Inertia-Partial-Data'));
                $page->setProps(array_intersect_key($page->getProps(), $propList));
            }
        }
        return jsonResponse($response, $page);
    }

    public static function getAssetVersion(): string
    {
        if ($f = config('manifest')) {
            if ($h = hash_file('md5', $f)) {
                return $h;
            }
            return 'Manifest-hashing-failed';
        }
        return 'Manifest-not-provided';
    }

    public static function getSharedData(): array
    {
        $msg = null;
        $flashData = [];
        if ($flash = Session::getFlash()) {
            foreach (['danger', 'warning', 'info', 'success'] as $v) {
                if (!empty($flash[$v])) {
                    $msg = ['v' => $v, 'm' => $flash[$v]];
                    unset($flash[$v]);
                }
            }
            $flashData = $flash;
        }
        return array_merge([
            'message' => $msg,
            'server_conf' => function () {
                $conf = [
                    'time' => date('l, F d, Y | h:i:s A'),
                    'version' => APP_VERSION,
                ];

                if ($user = UserIdentifier::getUser()) {
                    $conf['user'] = [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'role' => $user['role'],
                    ];
                }
                return $conf;
            }], $flashData);
    }

    protected static function renderHtml(ResponseInterface $response, $page): ResponseInterface
    {
        $pageData = htmlspecialchars(json_encode($page));
        $inertiaHtml = "<div id=\"app\" data-page=\"$pageData\"></div>\n";
        $tpl = config('templates') . '/app.template.php';
        ob_start();
        if (file_exists($tpl)) {
            require $tpl;
        } else {
            echo $inertiaHtml;
        }
        $html = ob_get_contents();
        ob_end_clean();
        $response->getBody()->write($html);
        return $response;
    }
}
