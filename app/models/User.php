<?php

namespace app\models;

use app\core\Model;
use app\core\Utils;
use Exception;

class User extends Model {

    public function users($page_size, $offset) {
        $count = $this->db->count_query("SELECT count(*) as count FROM user");
        $sql = sprintf("SELECT id, username, name, last_name, email, auth, is_active, is_verified, ci, sex, birthdate, country, phone, address, avatar, ti_wallet FROM user ORDER BY username DESC LIMIT %u, %u", $offset, $page_size);

        return [
            $this->db->fetch_query($sql),
            $count
        ];
    }

    public function findUserByCredentials($username, $password) {
        $sql = sprintf("SELECT id, username, password, unique_hash, auth FROM user WHERE username = '%s'", $this->db->sql_escape($username));
//        $sql = sprintf("SELECT * FROM user WHERE username = '%s' AND is_active = 1", $this->db->sql_escape($username));
        $data = $this->db->unique_query($sql);

        if ($data && password_verify($password, $data["password"])) {
            return $data;
        } else {
            return null;
        }
    }

    public function create($username, $password, $email) {
        $hashPassword = password_hash($password, PASSWORD_BCRYPT); // Encriptar contraseña
        $unique_hash = Utils::generateRandomString(); // Generar unique_hash

        $sql = sprintf("INSERT INTO user (username, email, password, unique_hash) VALUES ('%s','%s','%s','%s')", $this->db->sql_escape($username), $this->db->sql_escape($email), $this->db->sql_escape($hashPassword), $this->db->sql_escape($unique_hash));
        $this->db->query($sql);
        return $this->db->affected_rows();
    }

    public function find($id, $username = "", $unique_hash = "") {
        if (!empty($username) && !empty($unique_hash)) {
            $sqlUser = sprintf("SELECT * FROM user WHERE id = %u AND username = '%s' AND unique_hash = '%s'", $this->db->sql_escape($id), $this->db->sql_escape($username), $this->db->sql_escape($unique_hash));
        } elseif (!empty($username) && empty($unique_hash)) {
            $sqlUser = sprintf("SELECT * FROM user WHERE id = %u AND username = '%s'", $this->db->sql_escape($id), $this->db->sql_escape($username));
        } elseif (empty($username) && !empty($unique_hash)) {
            $sqlUser = sprintf("SELECT * FROM user WHERE id = %u AND unique_hash = '%s'", $this->db->sql_escape($id), $this->db->sql_escape($unique_hash));
        } else {
            $sqlUser = sprintf("SELECT * FROM user WHERE id = %u", $this->db->sql_escape($id));
        }

        $user = $this->db->unique_query($sqlUser);

        if ($user) {
            return $user;
        }

        throw new Exception("Usuario no existe.", 404);
    }

    public function activate($id, $activate = true) {
        if ($activate) {
            $sql = sprintf("UPDATE user SET is_active = 1 WHERE id = %u;", $this->db->sql_escape($id));
        } else {
            $sql = sprintf("UPDATE user SET is_active = 0 WHERE id = %u;", $this->db->sql_escape($id));
        }
        $this->db->query($sql);

        return $this->db->affected_rows();
    }

    public function setAvatar($avatar, $id, $username, $unique_hash) {
        $sql = sprintf("UPDATE user SET avatar = '%s' WHERE id = %u AND username = '%s' AND unique_hash = '%s'", $this->db->sql_escape($avatar), $this->db->sql_escape($id), $this->db->sql_escape($username), $this->db->sql_escape($unique_hash));
        $this->db->query($sql);
        return $this->db->affected_rows();
    }

    public function setWallet($wallet, $id, $username, $unique_hash) {
        $sql = sprintf("UPDATE user SET ti_wallet = '%s' WHERE id = %u AND username = '%s' AND unique_hash = '%s'", $this->db->sql_escape($wallet), $this->db->sql_escape($id), $this->db->sql_escape($username), $this->db->sql_escape($unique_hash));
        $this->db->query($sql);
        return $this->db->affected_rows();
    }

    public function changePassword($old_password, $password, $id, $username, $unique_hash) {
        $sqlUser = sprintf("SELECT password FROM user WHERE id = %u AND username = '%s' AND unique_hash = '%s'", $this->db->sql_escape($id), $this->db->sql_escape($username), $this->db->sql_escape($unique_hash));
        $user = $this->db->unique_query($sqlUser);

        if (!password_verify($old_password, $user['password'])) {
            throw new Exception("Contraseña actual no coincide.", 400);
        }

        $hashPassword = password_hash($password, PASSWORD_BCRYPT);

        $sql = sprintf("UPDATE user SET password = '%s' WHERE id = %u AND username = '%s' AND unique_hash = '%s'", $this->db->sql_escape($hashPassword), $this->db->sql_escape($id), $this->db->sql_escape($username), $this->db->sql_escape($unique_hash));
        $this->db->query($sql);

        return $this->db->affected_rows();
    }

    public function setRole($id, $auth) {
        $sql = sprintf("UPDATE user SET auth = '%s' WHERE id = %u", $this->db->sql_escape($auth), $this->db->sql_escape($id));
        $this->db->query($sql);
        return $this->db->affected_rows();
    }

    public function sendCode($code, $id, $username, $unique_hash) {
        $time = strtotime("+5 min");
        $clef = implode('.', [
            $code,
            $time
        ]);

        $sql = sprintf("UPDATE user SET clef = '%s' WHERE id = %u AND username = '%s' AND unique_hash = '%s'", $this->db->sql_escape($clef), $this->db->sql_escape($id), $this->db->sql_escape($username), $this->db->sql_escape($unique_hash));
        $this->db->query($sql);
        return $this->db->affected_rows();
    }

    public function verify($clef, $code, $id, $username, $unique_hash) {
        $arrClef = explode('.', $clef);
        $codeClef = $arrClef[0];
        $timeClef = $arrClef[1];

        if (time() > $timeClef) {
            throw new Exception("El codigo ha expirado.", 400);
        }

        if ($codeClef == $code) {
            $sql = sprintf("UPDATE user SET is_verified = 1, clef = null WHERE id = %u AND username = '%s' AND unique_hash = '%s'", $this->db->sql_escape($id), $this->db->sql_escape($username), $this->db->sql_escape($unique_hash));
            $this->db->query($sql);
            return $this->db->affected_rows();
        }

        throw new Exception("El codigo no es correcto.", 400);
    }

}