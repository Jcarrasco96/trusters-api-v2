<?php

namespace app\core;

use Exception;

class Validators {

    public function validateUsername($username) {
        if (!preg_match("/^[a-zA-Z0-9]+$/", $username)) {
            throw new Exception("Nombre de usuario no valido.", 400);
        }
    }

    public function validatePasswordMatch($password1, $password2) {
        if ($password1 != $password2) {
            throw new Exception("Contraseñas no coinciden.", 400);
        }
    }

    public function validateEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email no valido.", 400);
        }
    }

    public function validateSex($sex) {
        if ($sex != 'M' && $sex != 'F') {
            throw new Exception("Sexo no valido.", 400);
        }
    }

    public function validateIsSet($message, $data, ...$fields) {
        foreach ($fields as $field) {
            if (!isset($data[$field])) {
                throw new Exception($message, 400);
            }
        }
    }

    public function validateNotEmpty(...$fields) {
        foreach ($fields as $field) {
            if (empty($field)) {
                throw new Exception("Verifique los campos vacios.", 400);
            }
        }
    }

    public function validateIsNumeric($number) {
        if (!is_numeric($number)) {
            throw new Exception("Verifique que el campo sea un numero.", 400);
        }
    }

    public function isAuth(...$auth) {
        $utils = new Utils();
        $userModel = new User();

        $token = $utils->token();

        $user = $userModel->find($token['id'], $token['username'], $token['unique_hash']);
        if (!in_array($user['auth'], $auth)) {
            throw new Exception("El usuario '{$user['username']}' no tiene acceso a este recurso", 403);
        }
    }

    public function isAdmin() {
        $this->isAuth(App::$config['roles']['admin']);
    }

    public function isPoster() {
        $this->isAuth(App::$config['roles']['normal'], App::$config['roles']['admin']);
    }

}