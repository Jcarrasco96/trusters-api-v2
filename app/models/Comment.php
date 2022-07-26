<?php

namespace app\models;

use app\core\Model;

class Comment extends Model {

    public function index($id) {
        $page_size = isset($_GET['page_size']) ? (int)$_GET['page_size'] : 20;
        $offset = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($offset - 1) * $page_size;

        $count = $this->db->count_query(sprintf("SELECT count(*) as count FROM comment WHERE post_id = %u", $this->db->sql_escape($id)));

        $sql = sprintf("SELECT comment.id, comment.content, comment.post_id, user.id as 'user_id', user.username, user.name, user.last_name, user.email, user.is_verified, user.phone, user.avatar FROM comment, user WHERE post_id = %u AND comment.user_id = user.id LIMIT %u, %u", $this->db->sql_escape($id), $offset, $page_size);
        $comments = $this->db->fetch_query($sql);

        foreach ($comments as $key => $value) {
            $comments[$key]['name'] = $this->getName($value);
            unset($comments[$key]['username']);
            unset($comments[$key]['last_name']);
        }

        return [
            'count' => (int)$count,
            'pages' => ceil($count / $page_size),
            'comments' => $comments,
        ];
    }

    public function create($id, $token_id, $content) {
        $sql = sprintf("INSERT INTO comment (content, post_id, user_id) VALUES ('%s','%u','%u')", $this->db->sql_escape($content), $id, $token_id);
        $this->db->query($sql);
        return $this->db->affected_rows();
    }

}