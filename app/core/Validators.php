<?php

namespace app\core;

use app\models\User;
use Exception;

class Validators
{

    /**
     * @throws Exception
     */
    public static function validateUsername($username): void
    {
        if (!preg_match("/^[a-zA-Z0-9]+$/", $username)) {
            throw new Exception("Nombre de usuario no valido.", 400);
        }
    }

    /**
     * @throws Exception
     */
    public static function validatePasswordMatch($password1, $password2): void
    {
        if ($password1 != $password2) {
            throw new Exception("Contraseñas no coinciden.", 400);
        }
    }

    /**
     * @throws Exception
     */
    public static function validateEmail($email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email no valido.", 400);
        }
    }

    /**
     * @throws Exception
     */
    public static function validateSet($message, $data, ...$fields): void
    {
        foreach ($fields as $field) {
            if (!isset($data[$field])) {
                throw new Exception($message, 400);
            }
        }
    }

    /**
     * @throws Exception
     */
    public static function validateNotEmpty(...$fields): void
    {
        foreach ($fields as $field) {
            if (empty($field)) {
                throw new Exception("Verifique los campos vacios.", 400);
            }
        }
    }

    /**
     * @throws Exception
     */
    public static function validateIsNumeric($number): void
    {
        if (!is_numeric($number)) {
            throw new Exception("Verifique que el campo sea un numero.", 400);
        }
    }

    /**
     * @throws Exception
     */
    public static function isAuth($id, $username, ...$auth): void
    {
        $userModel = new User();

        $user = $userModel->find($id, $username);

        if (!in_array($user['auth'], $auth)) {
            throw new Exception("El usuario '{$user['username']}' no tiene acceso a este recurso", 403);
        }
    }

    /**
     * @throws Exception
     */
    public static function isAdmin($id, $username): void
    {
        self::isAuth($id, $username, App::$config['roles']['admin']);
    }

    /**
     * @throws Exception
     */
    public static function isDownloader($id, $username): void
    {
        self::isAuth($id, $username, App::$config['roles']['normal'], App::$config['roles']['admin']);
    }

    /**
     * @throws Exception
     */
    public static function validateRole($auth): void
    {
        $roleNumbers = array_values(App::$config['roles']);

        if (!in_array($auth, $roleNumbers)) {
            throw new Exception("Role not found", 404);
        }
    }

}