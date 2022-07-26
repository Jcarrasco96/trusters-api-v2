<?php

namespace app\controllers;

use app\core\App;
use app\core\Controller;
use app\core\Database;
use app\core\Utils;
use app\core\Validators;
use app\models\User;
use Exception;

class UserController extends Controller {

    public function index() {
        $model = new User();
        $token = Utils::token();

        Validators::isAdmin($token['id'], $token['username'], $token['unique_hash']);

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

    public function current() {
        $model = new User();
        $db = new Database();
        $token = Utils::token();

        $user = $model->find($token['id'], $token['username'], $token['unique_hash']);

        unset($user['password']);
        unset($user['unique_hash']);
        unset($user['clef']);

        $sqlCountPosts = sprintf("SELECT count(*) AS COUNT FROM post WHERE user_id = %u", $db->sql_escape($token['id']));
        $sqlCountComments = sprintf("SELECT count(*) AS COUNT FROM post INNER JOIN comment ON post.id = comment.post_id AND post.user_id = %u", $db->sql_escape($token['id']));

        $user['posts'] = $db->count_query($sqlCountPosts);
        $user['comments'] = $db->count_query($sqlCountComments);

        return $user;
    }

    public function generateAvatar() {
        //        $model = new User();
        //        $token = Utils::token();

        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($i = 0; $i < strlen($characters); $i++) {
            $path = Utils::generateAvatar($characters[$i]);
        }

        //        $type = 'png';
        //        $avatar = Utils::generateRandomString();

        $avatars = [
            'o' => $path,
            //            'r_100_100' => Utils::resizeImage($path, "{$avatar}_r_100_100.{$type}", $type),
            //            'r_500_500' => Utils::resizeImage($path, "{$avatar}_r_500_500.{$type}", $type, 500, 500),
        ];

        // unlink($avatar_o);

        // $model->setAvatar($avatars['r_100_100'], $token['id'], $token['username'], $token['unique_hash']);

        return $avatars;
    }

    public function activate($id) {
        $model = new User();
        $token = Utils::token();

        Validators::isAdmin($token['id'], $token['username'], $token['unique_hash']);

        $rows = $model->activate($id);

        if ($rows == 1) {
            return [
                'status'  => 200,
                'message' => 'Usuario activado'
            ];
        }

        throw new Exception("No se pudo activar el usuario, puede que ya se encuentre activo.", 400);
    }

    public function desactivate($id) {
        $model = new User();
        $token = Utils::token();

        Validators::isAdmin($token['id'], $token['username'], $token['unique_hash']);

        $rows = $model->activate($id, false);

        if ($rows == 1) {
            return [
                'status'  => 200,
                'message' => 'Usuario desactivado'
            ];
        }

        throw new Exception("No se pudo desactivar el usuario, puede que ya se encuentre inactivo.", 400);
    }

    public function avatar() {
        $model = new User();
        $token = Utils::token();

        if (!isset($_FILES['avatar'])) {
            throw new Exception("Debe subir un archivo", 400);
        }

        $type = strtolower(substr(strrchr($_FILES["avatar"]["type"], "/"), 1));
        $avatar = Utils::generateRandomString();

        $avatar_o = "media/{$avatar}_o.{$type}";

        copy($_FILES["avatar"]["tmp_name"], $avatar_o);

        $avatars = [
            'avatar' => Utils::resizeImage($avatar_o, "{$avatar}_r100.{$type}", $type),
            'r500' => Utils::resizeImage($avatar_o, "{$avatar}_r500.{$type}", $type, 500, 500),
        ];

        unlink($avatar_o);

        $rows = $model->setAvatar($avatars['avatar'], $token['id'], $token['username'], $token['unique_hash']);

        if ($rows == 1) {
            return $avatars;
        }

        throw new Exception("No se pudo cambiar la foto de perfil.", 400);
    }

    public function wallet() {
        $model = new User();
        $token = Utils::token();

        Validators::validateIsSet("Verifique los datos de la billetera electrónica.", $this->dataJson, 'ti_wallet');

        $rows = $model->setWallet($this->dataJson['ti_wallet'], $token['id'], $token['username'], $token['unique_hash']);

        if ($rows == 1) {
            return [
                'status' => 200,
                'message' => 'Billetera electrónica modificada correctamente.'
            ];
        } elseif ($rows == 0) {
            return [
                'status' => 400,
                'message' => 'Debe escoger otra billetera electrónica.'
            ];
        }

        throw new Exception("No se pudo cambiar la billetera electrónica.", 400);
    }

    public function changePassword() {
        $model = new User();
        $token = Utils::token();

        Validators::validateIsSet("Verifique los datos del afiliado tengan formato correcto", $this->dataJson, "old_password", "password", "password2");
        Validators::validatePasswordMatch($this->dataJson["password"], $this->dataJson["password2"]);
        Validators::validateNotEmpty($this->dataJson["old_password"], $this->dataJson["password"], $this->dataJson["password2"]);

        $rows = $model->changePassword($this->dataJson['old_password'], $this->dataJson['password'], $token['id'], $token['username'], $token['unique_hash']);

        if ($rows == 1) {
            return [
                'status' => 200,
                'message' => 'Contraseña cambiada correctamente.'
            ];
        }

        throw new Exception("No se pudo cambiar la contraseña.", 400);
    }

    public function setRole($id) {
        $model = new User();
        $token = Utils::token();

        Validators::isAdmin($token['id'], $token['username'], $token['unique_hash']);
        Validators::validateIsSet("Error Processing Request", $this->dataJson, 'auth');
        Validators::validateIsNumeric($this->dataJson['auth']);
        Validators::validateRole($this->dataJson['auth']);

        $rows = $model->setRole($id, $this->dataJson['auth']);

        if ($rows == 1) {
            return [
                'status'  => 200,
                'message' => 'Role cambiado correctamente'
            ];
        }

        throw new Exception("No se pudo cambiar el rol a este usuario, puede que ya tenga este rol.", 400);
    }

    public function sendCode() {
        $model = new User();
        $token = Utils::token();

        $user = $model->find($token['id'], $token['username'], $token['unique_hash']);

        if (isset($user['is_verified']) && $user['is_verified']) {
            return [
                'status' => 200,
                'message' => 'Este usuario ya ha sido verificado.',
            ];
        }

        $code = Utils::generateCode(6);

        $sendCode = $model->sendCode($code, $token['id'], $token['username'], $token['unique_hash']);
        $sendMail = Utils::sendMail($user['email'], 'Codigo de verificacion', "El codigo de verificacion del correo {$user['email']} es <h1>{$code}</h1>");

        if ($sendCode == 1 && $sendMail) {
            return [
                'status' => 200,
                'message' => 'Codigo enviado a ' . $user['email'],
            ];
        }

        throw new Exception('No se pudo enviar el codigo de verificacion.', 400);
    }

    public function verify() {
        $model = new User();
        $token = Utils::token();

        $user = $model->find($token['id'], $token['username'], $token['unique_hash']);

        if (isset($user['is_verified']) && $user['is_verified']) {
            return [
                'status' => 200,
                'message' => 'Este usuario ya ha sido verificado.',
            ];
        }

        Validators::validateIsSet("Usuario no tiene un codigo de verificacion valido.", $user, 'clef');

        $rows = $model->verify($user['clef'], $this->dataJson['code'], $token['id'], $token['username'], $token['unique_hash']);

        if ($rows == 1) {
            return [
                'status' => 200,
                'message' => 'Usuario verificado correctamente.'
            ];
        }

        throw new Exception("El usuario no ha solicitado validar su cuenta.", 400);
    }

}