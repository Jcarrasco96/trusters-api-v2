<?php

namespace app\controllers;

use app\core\App;
use app\core\Controller;
use app\core\JWT;
use app\core\Utils;
use app\core\Validators;
use app\models\User;
use Exception;

class AuthController extends Controller {

    public function login() {
        $model = new User();

        Validators::validateSet("Las credenciales son incorrectas", $this->dataJson, 'username', 'password');

        $id = $model->findUserByCredentials($this->dataJson['username'], $this->dataJson['password']);

        if ($id != 0) {
            $dbResult = $model->login($id);

            $payloadArray = [
                'id'          => $dbResult['id'],
                'username'    => $dbResult['username'],
                'unique_hash' => $dbResult['unique_hash'],
            ];

            $token = JWT::encode($payloadArray, App::$config['jwt']['serverkey']);

            return [
                'id'    => $dbResult['id'],
                'auth'  => $dbResult['auth'],
                'token' => $token,
            ];
        }

        return [
            "status"  => 400,
            "message" => 'Nombre de usuario o contraseña incorrectos',
        ];
    }

    public function register() {
        $model = new User();

        Validators::validateSet("Verifique los datos", $this->dataJson, "username", "password", "password2", "email");
        Validators::validatePasswordMatch($this->dataJson["password"], $this->dataJson["password2"]);
        Validators::validateEmail($this->dataJson['email']);
        Validators::validateUsername($this->dataJson['username']);

        $dbResult = $model->create($this->dataJson['username'], $this->dataJson['password'], $this->dataJson['email']);

        if ($dbResult == 1) {
            Utils::sendMail($this->dataJson['email'], "Cuenta creada correctamente.", "Bienvenido a nuestra comunidad {$this->dataJson['username']}\n\nEstamos encantados de que formes parte de nuestro grupo.\n\nSaludos.");

            return [
                "status"  => 201,
                "message" => "Usuario registrado!"
            ];
        } elseif ($dbResult == -1) {
            throw new Exception("Ya existe este usuario o los datos son incorrectos", 400);
        } else {
            throw new Exception("Error del servidor'", 500);
        }
    }

    public function logout() {
        $model = new User();
        $token = Utils::token();

        $update = $model->logout($token['id'], $token['username'], $token['unique_hash']);

        if ($update) {
            return [
                'status'  => 200,
                'message' => 'Logout'
            ];
        }

        throw new Exception("No se desautentifico", 400);
    }

}
