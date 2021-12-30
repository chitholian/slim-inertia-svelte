<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Session implements MiddlewareInterface
{
    private static array $inCookies = [];
    private static array $outCookies = [];
    private static array $rawInCookies = [];

    public const CIPHER_ALG = 'aes-128-cbc';

    public static function deleteByPrefix($prefix)
    {
        if ($path = session_save_path()) {
            foreach (glob($path . "/sess_$prefix*") as $item) {
                unlink($item);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        session_save_path(config('cache_path', sys_get_temp_dir()));
        session_name('sid');
        session_set_cookie_params(['samesite' => 'Strict', 'httponly' => true]);
        session_start();
        if (rand(1, 100) < 5) session_gc();
        static::$rawInCookies = $request->getCookieParams();
        $response = $handler->handle($request);
        // Set Cookies.
        $key = config('app_key', 'Key-Not-Provided');
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(static::CIPHER_ALG));
        foreach (static::$outCookies as $name => $cookie) {
            if ($data = openssl_encrypt($cookie['value'], static::CIPHER_ALG, $key, OPENSSL_RAW_DATA, $iv)) {
                setcookie($name, base64_encode($iv . $data), array_merge(['samesite' => 'Lax', 'httponly' => true], $cookie['options']));
            }
        }
        return $response;
    }

    public static function regenerate($prefix = '', $clean = false)
    {
        if ($clean) static::cleanAll();
        $newId = session_create_id($prefix);
        session_commit();
        ini_set('session.use_strict_mode', 0);
        session_id($newId);
        session_start();
    }

    public static function put($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public static function clear($key)
    {
        if (isset($_SESSION[$key])) unset($_SESSION[$key]);
    }

    public static function cleanAll()
    {
        $_SESSION = [];
    }

    public static function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function setFlash($flashData)
    {
        static::put('flashData', $flashData);
    }

    public static function getFlash($clear = true)
    {
        $f = static::get('flashData', []);
        if ($clear) static::setFlash([]);
        return $f;
    }

    public static function isActive(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    public static function setCookie($name, $value, array $options = []): bool
    {
        static::$outCookies[$name] = [
            'value' => $value,
            'options' => $options,
        ];
        return true;
    }

    public static function getCookie($name, $default = null)
    {
        if (isset(static::$inCookies[$name])) return static::$inCookies[$name];
        if (isset(static::$rawInCookies[$name])) {
            $key = config('app_key', 'Key-Not-Provided');
            $data = base64_decode(static::$rawInCookies[$name]);
            $ivLen = openssl_cipher_iv_length(static::CIPHER_ALG);
            $iv = substr($data, 0, $ivLen);
            $data = substr($data, $ivLen);
            if (($data = openssl_decrypt($data, static::CIPHER_ALG, $key, OPENSSL_RAW_DATA, $iv)) !== false) {
                return self::$inCookies[$name] = $data;
            }
        }
        return $default;
    }
}
