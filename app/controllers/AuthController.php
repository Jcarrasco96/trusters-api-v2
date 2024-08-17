<?php

namespace app\controllers;

use app\core\App;
use app\core\Controller;
use app\core\JWT;
use app\core\Utils;
use app\core\Validators;
use app\models\User;
use Exception;

class AuthController extends Controller
{

    /**
     * @throws Exception
     */
    public function login(): array
    {
        $model = new User();

        Validators::validateSet("Las credenciales son incorrectas", $this->dataJson, 'username', 'password');

        $data = $model->findUserByCredentials($this->dataJson['username'], $this->dataJson['password']);

        if (empty($data)) {
            return ["status_code" => 400, "message" => 'Nombre de usuario o contraseña incorrectos'];
        }

        $token = JWT::encode([
            'id' => $data['id'],
            'username' => $data['username'],
            'auth' => $data['auth'],
        ], App::$config['jwt']['serverkey']);

        return [
            'id' => $data['id'],
            'username' => $data['username'],
            'auth' => $data['auth'],
            'token' => $token,
        ];
    }

    /**
     * @throws Exception
     */
    public function register(): array
    {
        $model = new User();

        Validators::validateSet("Verifique los datos", $this->dataJson, "username", "password", "password2", "email");
        Validators::validatePasswordMatch($this->dataJson["password"], $this->dataJson["password2"]);
        Validators::validateEmail($this->dataJson['email']);
        Validators::validateUsername($this->dataJson['username']);

        $dbResult = $model->create($this->dataJson['username'], $this->dataJson['password'], $this->dataJson['email']);

        if ($dbResult == 1) {
            Utils::sendMail($this->dataJson['email'], "Cuenta creada correctamente.", "Bienvenido a nuestra comunidad {$this->dataJson['username']}\n\nEstamos encantados de que formes parte de nuestro grupo.\n\nSaludos.");

            return ["status_code" => 201, "message" => "Usuario registrado!"];
        }

        if ($dbResult == -1) {
            throw new Exception("Ya existe este usuario o los datos son incorrectos", 400);
        }

        throw new Exception("Error del servidor'", 500);
    }

    /**
     * @throws Exception
     */
    public function logout(): array
    {
        $token = Utils::token();

        if (!empty($token)) {
            return ['status_code' => 200, 'message' => 'Logout'];
        }

        throw new Exception("No se ha cerrado la sesión", 400);
    }

}
