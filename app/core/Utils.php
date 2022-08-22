<?php

namespace app\core;

use DateTime;
use Exception;

class Utils {

    public static function generateRandomString($lenght = 13, $hex = false) {
        $str = null;
        $character = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $lenght = $hex ? ceil($lenght / 2) : $lenght;

        for ($i = 0; $i < $lenght; $i++) {
            $radom = rand(0, strlen($character) - 1);
            $str .= $character[$radom];
        }
        return $hex ? bin2hex($str) : $str;
    }

    public static function generateCode($lenght = 4) {
        $str = null;
        $character = '1234567890';

        for ($i = 0; $i < $lenght; $i++) {
            $radom = rand(0, strlen($character) - 1);
            $str .= $character[$radom];
        }
        return $str;
    }

    public static function token() {
        $headers = array_change_key_case(getallheaders(), CASE_LOWER);

        if (isset($headers['authorization'])) {
            $token = trim(str_replace('Bearer ', '', $headers['authorization']));

            $payload = JWT::decode($token, App::$config['jwt']['serverkey'], ['HS256']);

            if (!isset($payload->id)) {
                throw new Exception("Debe de proveer un token valido", 500);
            }

            $returnArray = [
                'id' => isset($payload->id) ? $payload->id : 0,
                'username' => isset($payload->username) ? $payload->username : '',
                'unique_hash' => isset($payload->unique_hash) ? $payload->unique_hash : '',
            ];
            if (isset($payload->exp)) {
                $returnArray['exp'] = date(DateTime::ISO8601, $payload->exp);
            }

            return $returnArray;
        }

        throw new Exception('Header Authorization not found.', 400);
    }

    public static function sendMail($to, $subject, $body) {
        if (App::$config['env'] == 'dev') {
            file_put_contents("mail/" . date('Ymd_His') . ".eml", "Subject: {$subject}\nFrom: Trusters Group <noreply@trusters.cmsagency.com.es>\nTo: {$to}\nMIME-Version: 1.0\nContent-Type: text/html; charset=utf-8\nContent-Transfer-Encoding: quoted-printable\n\n{$body}");
            return true;
        } else {
            return mail($to, $subject, $body, "From: Trusters Group <noreply@trusters.cmsagency.com.es>\n");
        }
    }

    public static function generateAvatar($character) {
        $rs = self::generateRandomString();
        $path = 'media' . DIRECTORY_SEPARATOR . $character . $rs . '_' . time() . ".png";

        $width = 500;
        $height = 500;
        $text_size = 250;

        $image = imagecreate($width, $height);

        imagecolorallocate($image, rand(50, 200), rand(50, 200), rand(50, 200));

        $font = FONT_PATH . 'unispace bd.ttf';

        $textcolor = imagecolorallocate($image, rand(230, 250), rand(230, 250), rand(230, 250));

        $bbox = imagettfbbox($text_size, 0, $font, strtoupper($character));

        $tx = $width / 2 - $bbox[2] / 2;
        $ty = $width / 2 - $bbox[7] / 2;

        imagettftext($image, $text_size, 0, $tx, $ty, $textcolor, $font, strtoupper($character));

        imagepng($image, $path);
        imagedestroy($image);

        return $path;
    }

    public static function resizeImage($avatar, $fn, $type, $width, $height) {
        switch ($type) {
            case 'bmp':
                $img = imagecreatefromwbmp($avatar);
                break;
            case 'gif':
                $img = imagecreatefromgif($avatar);
                break;
            case 'jpg':
            case 'jpeg':
                $img = imagecreatefromjpeg($avatar);
                break;
            case 'png':
                $img = imagecreatefrompng($avatar);
                break;
            default:
                throw new Exception("Unsupported picture type", 400);
        }

        $radio_thumb = $width / $height;
        $xx = imagesx($img);
        $yy = imagesy($img);
        $radio_original = $xx / $yy;

        if ($radio_original >= $radio_thumb) {
            $yo = $yy;
            $xo = ceil(($yo * $width) / $height);
            $xo_ini = ceil(($xx - $xo) / 2);
            $yo_ini = 0;
        } else {
            $xo = $xx;
            $yo = ceil(($xo * $height) / $width);
            $yo_ini = ceil(($yy - $yo) / 2);
            $xo_ini = 0;
        }

        $rimg = imagecreatetruecolor($width, $height);

        if ($type == 'png' || $type == 'gif') {
            imagecolortransparent($rimg, imagecolorallocatealpha($rimg, 0, 0, 0, 127));
            imagealphablending($rimg, false);
            imagesavealpha($rimg, true);
        }

        imagecopyresampled($rimg, $img, 0, 0, $xo_ini, $yo_ini, $width, $height, $xo, $yo);

        imagepng($rimg, "media/profile/{$fn}");

        imagedestroy($img);
        imagedestroy($rimg);

        return $fn;
    }

}