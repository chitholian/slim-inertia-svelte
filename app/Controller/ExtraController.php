<?php

namespace App\Controller;

use App\Middleware\Session;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ExtraController extends BaseController
{
    public function captcha(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $permitted_chars = '0123456789';
        function generate_string($input, $strength = 10): string
        {
            $input_length = strlen($input);
            $random_string = '';
            for ($i = 0; $i < $strength; $i++) {
                $random_character = $input[mt_rand(0, $input_length - 1)];
                $random_string .= $random_character;
            }
            return $random_string;
        }

        $image = imagecreatetruecolor(200, 40);
        imageantialias($image, true);
        $bgColor = imagecolorallocate($image, 255, 90, 0);
        $fgColor = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $bgColor);
        $string_length = 6;
        $captcha_string = generate_string($permitted_chars, $string_length);
        for ($i = 0; $i < $string_length; $i++) {
            $letter_space = 170 / $string_length;
            $initial = 20;
            imagettftext($image, 24, 0, $initial + $i * $letter_space, 30, $fgColor, __DIR__ . '/../../public/assets/fonts/NotoMono-Regular.ttf', $captcha_string[$i]);
        }
        ob_start();
        imagepng($image);
        $data = ob_get_contents();
        ob_end_clean();
        imagedestroy($image);
        Session::put('captcha', $captcha_string);
        $response->getBody()->write($data);
        return $response->withHeader('Cache-Control', 'no-store,no-cache, must-revalidate')
            ->withHeader('Content-Type', 'image/png');
    }

    public function myIP(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = $request->getServerParams();
        return jsonResponse($response, [
            'ip' => $params['REMOTE_ADDR'] ?? null,
            'port' => $params['REMOTE_PORT'] ?? null,
            'user_agent' => $params['HTTP_USER_AGENT'] ?? null,
        ]);
    }

    public static function verifyCaptcha($captcha): bool
    {
        $valid = ($c = Session::get('captcha')) && $captcha === $c;
        Session::clear('captcha');
        return $valid;
    }
}
