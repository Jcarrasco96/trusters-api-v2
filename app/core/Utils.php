<?php

namespace app\core;

use Exception;

class Utils
{

    public static function randomString($lenght = 13, $hex = false): ?string
    {
        $str = null;
        $character = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $lenght = $hex ? ceil($lenght / 2) : $lenght;

        for ($i = 0; $i < $lenght; $i++) {
            $radom = rand(0, strlen($character) - 1);
            $str .= $character[$radom];
        }
        return $hex ? bin2hex($str) : $str;
    }

    public static function code($lenght = 4): ?string
    {
        $str = null;
        $character = '1234567890';

        for ($i = 0; $i < $lenght; $i++) {
            $radom = rand(0, strlen($character) - 1);
            $str .= $character[$radom];
        }
        return $str;
    }

    /**
     * @throws Exception
     */
    public static function token(): array
    {
        $headers = array_change_key_case(getallheaders());
        $authorization = $headers['authorization'] ?? null;

        if (!$authorization) {
            throw new Exception('Header Authorization not found.', 400);
        }

        $token = trim(str_replace('Bearer ', '', $authorization));
        $payload = JWT::decode($token, App::$config['jwt']['serverkey'], ['HS256']);

        if (!isset($payload->id)) {
            throw new Exception("Debe de proveer un token valido", 500);
        }

        return [
            'id' => $payload->id,
            'username' => $payload->username ?? '',
            'exp' => isset($payload->exp) ? date('Y-m-d H:i:s', $payload->exp) : null,
        ];
    }

    public static function sendMail($to, $subject, $body): bool
    {
        if (App::$config['env'] == 'dev') {
            file_put_contents("mail/" . date('Ymd_His') . ".eml", "Subject: $subject\nFrom: Trusters Group <noreply@downloader.jcarrasco96.com>\nTo: $to\nMIME-Version: 1.0\nContent-Type: text/html; charset=utf-8\nContent-Transfer-Encoding: quoted-printable\n\n$body");
            return true;
        } else {
            return mail($to, $subject, $body, "From: Downloader Group <noreply@downloader.jcarrasco96.com>\n");
        }
    }

}