<?php

namespace app\models;

use app\core\Model;
use Exception;

class User extends Model
{

    public function users($page_size, $offset): array
    {
        $count = $this->db->count_query("SELECT count(*) as count FROM user");
        $sql = sprintf("SELECT id, username, name, email, auth, is_active, is_verified FROM user ORDER BY username DESC LIMIT %u, %u", $offset, $page_size);

        return [
            $this->db->fetch_query($sql),
            $count
        ];
    }

    public function findUserByCredentials($username, $password): false|array|null
    {
        $sql = sprintf("SELECT id, username, password, auth FROM user WHERE username = '%s'", $this->db->sql_escape($username));
//        $sql = sprintf("SELECT id, username, password, auth FROM user WHERE username = '%s' AND is_active = 1", $this->db->sql_escape($username));
        $data = $this->db->unique_query($sql);

        if ($data && password_verify($password, $data["password"])) {
            unset($data["password"]);

            return $data;
        }

        return null;
    }

    public function create($username, $password, $email): int|string
    {
        $hashPassword = password_hash($password, PASSWORD_BCRYPT); // Encriptar contraseña

        if (empty($hashPassword)) {
            return -1;
        }

        $sql = sprintf("INSERT INTO user (username, email, password) VALUES ('%s','%s','%s')", $this->db->sql_escape($username), $this->db->sql_escape($email), $this->db->sql_escape($hashPassword));
        $this->db->query($sql);
        return $this->db->affected_rows();
    }

    /**
     * @throws Exception
     */
    public function update($name, $email, $id, $username): int|string
    {
        $sqlUser = sprintf("SELECT * FROM user WHERE id = %u AND username = '%s'", $this->db->sql_escape($id), $this->db->sql_escape($username));
        $user = $this->db->unique_query($sqlUser);

        if (!$user) {
            throw new Exception("Verifique los datos del perfil", 400);
        }

        $is_verified = $user['email'] == $email && $user['is_verified'] == 1 ? 1 : 0;

        $sql = sprintf("UPDATE user SET name = '%s', email = '%s', is_verified = %u WHERE id = %u AND username = '%s'", $this->db->sql_escape($name), $this->db->sql_escape($email), $is_verified, $this->db->sql_escape($id), $this->db->sql_escape($username));

        $this->db->query($sql);

        return $this->db->affected_rows();
    }

    /**
     * @throws Exception
     */
    public function find($id, $username = ""): array
    {
        if (!empty($username)) {
            $sqlUser = sprintf("SELECT * FROM user WHERE id = %u AND username = '%s'", $this->db->sql_escape($id), $this->db->sql_escape($username));
        } else {
            $sqlUser = sprintf("SELECT * FROM user WHERE id = %u", $this->db->sql_escape($id));
        }

        $user = $this->db->unique_query($sqlUser);

        if ($user) {
            unset($user["password"]);
            return $user;
        }

        throw new Exception("Usuario no existe.", 404);
    }

    public function activate($id, $activate = true): int|string
    {
        if ($activate) {
            $sql = sprintf("UPDATE user SET is_active = 1 WHERE id = %u;", $this->db->sql_escape($id));
        } else {
            $sql = sprintf("UPDATE user SET is_active = 0 WHERE id = %u;", $this->db->sql_escape($id));
        }

        $this->db->query($sql);

        return $this->db->affected_rows();
    }

    /**
     * @throws Exception
     */
    public function changePassword($old_password, $password, $id, $username): int|string
    {
        $sqlUser = sprintf("SELECT password FROM user WHERE id = %u AND username = '%s'", $this->db->sql_escape($id), $this->db->sql_escape($username));
        $user = $this->db->unique_query($sqlUser);

        if (!password_verify($old_password, $user['password'])) {
            throw new Exception("Contraseña actual no coincide.", 400);
        }

        $hashPassword = password_hash($password, PASSWORD_BCRYPT);

        $sql = sprintf("UPDATE user SET password = '%s' WHERE id = %u AND username = '%s'", $this->db->sql_escape($hashPassword), $this->db->sql_escape($id), $this->db->sql_escape($username));
        $this->db->query($sql);

        return $this->db->affected_rows();
    }

    public function setRole($id, $auth): int|string
    {
        $sql = sprintf("UPDATE user SET auth = '%s' WHERE id = %u", $this->db->sql_escape($auth), $this->db->sql_escape($id));
        $this->db->query($sql);
        return $this->db->affected_rows();
    }

    public function sendCode($code, $id, $username): int|string
    {
        $time = strtotime("+5 min");
        $clef = implode('.', [$code, $time]);

        $sql = sprintf("UPDATE user SET clef = '%s' WHERE id = %u AND username = '%s'", $this->db->sql_escape($clef), $this->db->sql_escape($id), $this->db->sql_escape($username));
        $this->db->query($sql);
        return $this->db->affected_rows();
    }

    /**
     * @throws Exception
     */
    public function verify($clef, $code, $id, $username): int|string
    {
        $arrClef = explode('.', $clef);
        $codeClef = $arrClef[0];
        $timeClef = $arrClef[1];

        if (time() > $timeClef) {
            throw new Exception("El codigo ha expirado.", 400);
        }

        if ($codeClef == $code) {
            $sql = sprintf("UPDATE user SET is_verified = 1, clef = null WHERE id = %u AND username = '%s'", $this->db->sql_escape($id), $this->db->sql_escape($username));
            $this->db->query($sql);
            return $this->db->affected_rows();
        }

        throw new Exception("El codigo no es correcto.", 400);
    }

}