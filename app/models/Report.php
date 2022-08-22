<?php

namespace app\models;

use app\core\Model;

class Report extends Model {

    public function create($post_id, $user_id) {
        $sqlSelect = sprintf("SELECT * FROM report WHERE post_id = '%s' AND user_id = '%s'", $this->db->sql_escape($post_id), $this->db->sql_escape($user_id));
        $report =  $this->db->unique_query($sqlSelect);

        if ($report) {
            return 0;
        } else {
            $sql = sprintf("INSERT INTO report (post_id, user_id) VALUES (%u, %u)", $this->db->sql_escape($post_id), $this->db->sql_escape($user_id));
            $this->db->query($sql);
            return $this->db->affected_rows();
        }
    }

}