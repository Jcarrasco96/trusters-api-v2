<?php

namespace app\controllers;

use app\core\App;
use app\core\Controller;
use app\core\JWT;
use app\core\Validators;
use app\models\User;
use Exception;

class AuthController extends Controller {

    public function behavior() {
        return [
            'access' => [],
        ];
    }

    public function index() {
        return [
            "status" => 201,
            "message" => "Usuario registrado"
        ];
    }

    public function login() {
        $model = new User();
        $validator = new Validators();

        $validator->validateIsSet("Las credenciales del afiliado deben estar definidas correctamente", $this->dataJson, 'username', 'password');

        $dbResult = $model->findUserByCredentials($this->dataJson['username'], $this->dataJson['password']);

        if ($dbResult != null) {
            // Procesar resultado de la consulta y crear un token
            $payloadArray = [];
            $payloadArray['id'] = $dbResult["id"];
            $payloadArray['username'] = $dbResult["username"];
            $payloadArray['unique_hash'] = $dbResult["unique_hash"];
            $payloadArray['auth'] = $dbResult["auth"];
//            $payloadArray['nbf'] = strtotime('-7 day');
//            $payloadArray['exp'] = strtotime("+7 day");
            $token = JWT::encode($payloadArray, App::$config['jwt']['serverkey']);

            return [
                "status" => 200,
                "id" => $dbResult["id"],
                "username" => $dbResult["username"],
                "token" => $token
            ];
        } else {
            throw new Exception("Nombre de usuario o contraseña inválidos", 400);
        }
    }


}
