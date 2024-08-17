<?php

namespace app\controllers;

use app\core\Controller;
use app\core\Database;
use app\core\Utils;
use app\core\Validators;
use app\models\User;
use Exception;

class UserController extends Controller
{

    /**
     * @throws Exception
     */
    public function index(): array
    {
        $model = new User();
        $token = Utils::token();

        Validators::isAdmin($token['id'], $token['username']);

        $page_size = isset($_GET['page_size']) ? (int)$_GET['page_size'] : 20;
        $offset = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($offset - 1) * $page_size;

        list($data, $count) = $model->users($page_size, $offset);

        return [
            'count' => (int)$count,
            'pages' => ceil($count / $page_size),
            'users' => $data,
        ];
    }

    /**
     * @throws Exception
     */
    public function current(): array
    {
        $model = new User();
        $db = new Database();
        $token = Utils::token();

        $user = $model->find($token['id'], $token['username']);

        unset($user['password']);
        unset($user['clef']);

        $sqlCountPosts = sprintf('SELECT count(*) AS COUNT FROM download WHERE user_id = %u', $db->sql_escape($token['id']));

        $user['downloads'] = $db->count_query($sqlCountPosts);

        return $user;
    }

    /**
     * @throws Exception
     */
    public function activate($id): array
    {
        $model = new User();
        $token = Utils::token();

        Validators::isAdmin($token['id'], $token['username']);

        $rows = $model->activate($id);

        if ($rows == 1) {
            return ['status_code' => 200, 'message' => 'Usuario activado'];
        }

        throw new Exception('No se pudo activar el usuario, puede que ya se encuentre activo.', 400);
    }

    /**
     * @throws Exception
     */
    public function desactivate($id): array
    {
        $model = new User();
        $token = Utils::token();

        Validators::isAdmin($token['id'], $token['username']);

        $rows = $model->activate($id, false);

        if ($rows == 1) {
            return ['status_code' => 200, 'message' => 'Usuario desactivado'];
        }

        throw new Exception('No se pudo desactivar el usuario, puede que ya se encuentre inactivo.', 400);
    }

    /**
     * @throws Exception
     */
    public function changePassword(): array
    {
        $model = new User();
        $token = Utils::token();

        Validators::validateSet('Verifique que los datos del afiliado tengan un formato correcto', $this->dataJson, 'old_password', 'password', 'password2');
        Validators::validatePasswordMatch($this->dataJson['password'], $this->dataJson['password2']);
        Validators::validateNotEmpty($this->dataJson['old_password'], $this->dataJson['password'], $this->dataJson['password2']);

        $rows = $model->changePassword($this->dataJson['old_password'], $this->dataJson['password'], $token['id'], $token['username']);

        if ($rows == 1) {
            return ['status_code' => 200, 'message' => 'Contraseña cambiada correctamente.'];
        }

        throw new Exception('No se pudo cambiar la contraseña.', 400);
    }

    /**
     * @throws Exception
     */
    public function setRole($id): array
    {
        $model = new User();
        $token = Utils::token();

        Validators::isAdmin($token['id'], $token['username']);
        Validators::validateSet('Error Processing Request', $this->dataJson, 'auth');
        Validators::validateIsNumeric($this->dataJson['auth']);
        Validators::validateRole($this->dataJson['auth']);

        $rows = $model->setRole($id, $this->dataJson['auth']);

        if ($rows == 1) {
            return ['status_code' => 200, 'message' => 'Role cambiado correctamente'];
        }

        throw new Exception('No se pudo cambiar el rol a este usuario, puede que ya tenga este rol.', 400);
    }

    /**
     * @throws Exception
     */
    public function sendCode(): array
    {
        $model = new User();
        $token = Utils::token();

        $user = $model->find($token['id'], $token['username']);

        if (isset($user['is_verified']) && $user['is_verified']) {
            return ['status_code' => 200, 'message' => 'Este usuario ya ha sido verificado.',];
        }

        $code = Utils::code(6);

        $sendCode = $model->sendCode($code, $token['id'], $token['username']);
        $sendMail = Utils::sendMail($user['email'], 'Codigo de verificacion', sprintf("El codigo de verificacion del correo %s es <h1>%s</h1>", $user['email'], $code));

        if ($sendCode == 1 && $sendMail) {
            return ['status_code' => 200, 'message' => 'Codigo enviado a ' . $user['email'],];
        }

        throw new Exception('No se pudo enviar el codigo de verificacion.', 400);
    }

    /**
     * @throws Exception
     */
    public function verify(): array
    {
        $model = new User();
        $token = Utils::token();

        $user = $model->find($token['id'], $token['username']);

        if (isset($user['is_verified']) && $user['is_verified']) {
            return ['status_code' => 200, 'message' => 'Este usuario ya ha sido verificado.',];
        }

        Validators::validateSet('Usuario no tiene un codigo de verificacion valido.', $user, 'clef');

        $rows = $model->verify($user['clef'], $this->dataJson['code'], $token['id'], $token['username']);

        if ($rows == 1) {
            return ['status_code' => 200, 'message' => 'Usuario verificado correctamente.'];
        }

        throw new Exception('El usuario no ha solicitado validar su cuenta.', 400);
    }

    /**
     * @throws Exception
     */
    public function update(): array
    {
        $model = new User();
        $token = Utils::token();

        Validators::validateSet('Verifique los datos del afiliado tengan formato correcto', $this->dataJson, 'name', 'email');
        Validators::validateEmail($this->dataJson['email']);

        $rows = $model->update($this->dataJson['name'], $this->dataJson['email'], $token['id'], $token['username']);

        if ($rows == 1) {
            return ['status_code' => 200, 'message' => 'Perfil modificado correctamente'];
        }

        if ($rows == 0) {
            return ['status_code' => 200, 'message' => 'No hay cambios de datos en el perfil'];
        }

        throw new Exception('Error en la base de datos', 500);
    }

}