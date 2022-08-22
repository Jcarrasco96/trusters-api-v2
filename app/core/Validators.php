<?php

namespace app\core;

use app\models\User;
use Exception;

class Validators {

    public static function validateUsername($username) {
        if (!preg_match("/^[a-zA-Z0-9]+$/", $username)) {
            throw new Exception("Nombre de usuario no valido.", 400);
        }
    }

    public static function validatePasswordMatch($password1, $password2) {
        if ($password1 != $password2) {
            throw new Exception("Contraseñas no coinciden.", 400);
        }
    }

    public static function validateEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email no valido.", 400);
        }
    }

    public static function validateSex($sex) {
        if ($sex != 'M' && $sex != 'F') {
            throw new Exception("Sexo no valido.", 400);
        }
    }

    public static function validateSet($message, $data, ...$fields) {
        foreach ($fields as $field) {
            if (!isset($data[$field])) {
                throw new Exception($message, 400);
            }
        }
    }

    public static function validateNotEmpty(...$fields) {
        foreach ($fields as $field) {
            if (empty($field)) {
                throw new Exception("Verifique los campos vacios.", 400);
            }
        }
    }

    public static function validateIsNumeric($number) {
        if (!is_numeric($number)) {
            throw new Exception("Verifique que el campo sea un numero.", 400);
        }
    }

    public static function isAuth($id, $username, $hash, ...$auth) {
        $userModel = new User();

        $user = $userModel->find($id, $username, $hash);

        if (!in_array($user['auth'], $auth)) {
            throw new Exception("El usuario '{$user['username']}' no tiene acceso a este recurso", 403);
        }
    }

    public static function isAdmin($id, $username, $hash) {
        self::isAuth($id, $username, $hash, App::$config['roles']['admin']);
    }

    public static function isPoster($id, $username, $hash) {
        self::isAuth($id, $username, $hash, App::$config['roles']['normal'], App::$config['roles']['admin']);
    }

    public static function validateRole($auth) {
        $roleNumbers = array_values(App::$config['roles']);

        if (!in_array($auth, $roleNumbers)) {
            throw new Exception("Role not found", 404);
        }
    }

}