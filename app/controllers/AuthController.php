<?php

namespace app\controllers;

use app\core\App;
use app\core\Controller;
use app\core\Database;
use app\core\JWT;
use app\core\Utils;
use app\core\Validators;
use app\models\User;
use Exception;

class AuthController extends Controller {

    public function login() {
        $model = new User();

        Validators::validateIsSet("Las credenciales del afiliado deben estar definidas correctamente", $this->dataJson, 'username', 'password');

        $dbResult = $model->findUserByCredentials($this->dataJson['username'], $this->dataJson['password']);

        if ($dbResult != null) {
            $payloadArray = [
                'id'          => $dbResult['id'],
                'username'    => $dbResult['username'],
                'unique_hash' => $dbResult['unique_hash'],
                'auth'        => $dbResult['auth'],
                //                'nbf' => strtotime('-7 day'),
                //                'exp' => strtotime("+7 day"),
            ];
            $token = JWT::encode($payloadArray, App::$config['jwt']['serverkey']);

            return [
                "status"      => 200,
                "id"          => $dbResult['id'],
                "username"    => $dbResult['username'],
                'unique_hash' => $dbResult['unique_hash'],
                'auth'        => $dbResult['auth'],
                "token"       => $token
            ];
        } else {
            return [
                "status"  => 400,
                "message" => 'Nombre de usuario o contraseña inválidos',
            ];
        }
    }

    public function register() {
        $model = new User();

        Validators::validateIsSet("Verifique los datos", $this->dataJson, "username", "password", "password2", "email");
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
        $token = Utils::token();

        $unique_hash = Utils::generateRandomString(); // Generar unique_hash

        $db = new Database();
        $sql = sprintf("UPDATE user SET unique_hash = '%s' WHERE id = %u AND username = '%s' AND unique_hash = '%s'", $db->sql_escape($unique_hash), $db->sql_escape($token['id']), $db->sql_escape($token['username']), $db->sql_escape($token['unique_hash']));

        $db->query($sql);

        if ($db->affected_rows() > 0) {
            return [
                'status' => 200,
                'message' => 'Logout'
            ];
        } else {
            throw new Exception("No se desautentifico", 400);
        }

    }

}
