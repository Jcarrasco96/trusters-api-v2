<?php

namespace app\models;

use app\core\Model;

class User extends Model {

    public function users($page_size, $offset) {
        $count = $this->db->countquery("SELECT count(*) as count FROM user");
        $sql = sprintf("SELECT * FROM user ORDER BY username DESC LIMIT %u, %u", $offset, $page_size);

        return [
            $this->db->fetchquery($sql),
            $count
        ];
    }

    public function findUserByCredentials($username, $password) {
        $sql = sprintf("SELECT * FROM user WHERE username = '%s'", $this->db->sql_escape($username));
        // $sql = sprintf("SELECT * FROM user WHERE username = '%s' AND is_active = 1", $db->sql_escape($username));
        $data = $this->db->uniquequery($sql);

        if ($data && password_verify($password, $data["password"])) {
            return $data;
        } else {
            return null;
        }
    }


}