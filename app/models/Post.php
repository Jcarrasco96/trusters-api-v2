<?php

namespace app\models;

use app\core\Model;
use Exception;

class Post extends Model {

    public function index($page_size, $offset) {
        $count = $this->db->count_query("SELECT count(*) as count FROM post");
        $sql = sprintf("SELECT post.id, post.content, post.created_at, post.status_post, user.id as 'user_id', user.username, user.name, user.last_name, user.email, user.is_verified, user.phone, user.avatar FROM post, user WHERE post.user_id = user.id ORDER BY created_at DESC LIMIT %u, %u", $offset, $page_size);
        $data = $this->db->fetch_query($sql);

        foreach ($data as $key => $value) {
            $data[$key]['name'] = $this->getName($value);
            unset($data[$key]['username']);
            unset($data[$key]['last_name']);
        }

        return [
            $data,
            $count
        ];
    }

    public function view($id) {
        $sql = sprintf("SELECT post.id, post.content, post.created_at, post.status_post, user.id as 'user_id', user.username, user.name, user.last_name, user.email, user.is_verified, user.phone, user.avatar FROM post, user WHERE post.user_id = user.id AND post.id = %u", $this->db->sql_escape($id));
        $post = $this->db->unique_query($sql);

        if (isset($post)) {
            $post['name'] = $this->getName($post);

            unset($post['username']);
            unset($post['last_name']);

            return $post;
        }

        throw new Exception("Post no encontrado", 404);
    }

    public function owner($page_size, $offset, $user_id) {
        $sqlCount = sprintf("SELECT count(*) as count FROM post WHERE user_id = %u", $this->db->sql_escape($user_id));
        $count = $this->db->count_query($sqlCount);

        $sql = sprintf("SELECT post.id, post.content, post.created_at, post.status_post, user.id as 'user_id', user.username, user.name, user.last_name, user.email, user.is_verified, user.phone, user.avatar FROM post, user WHERE post.user_id = user.id AND post.user_id = %u ORDER BY created_at DESC LIMIT %u, %u", $this->db->sql_escape($user_id), $offset, $page_size);
        $data = $this->db->fetch_query($sql);

        foreach ($data as $key => $value) {
            $data[$key]['name'] = $this->getName($value);
            unset($data[$key]['username']);
            unset($data[$key]['last_name']);
        }

        return [
            $data,
            $count
        ];
    }

    public function create($content, $user_id) {
        $sql = sprintf("INSERT INTO post (content, user_id) VALUES ('%s','%s')", $this->db->sql_escape($content), $this->db->sql_escape($user_id));
        $this->db->query($sql);

        $sqlPost = sprintf("SELECT post.id, post.content, post.created_at, post.status_post, user.id as 'user_id', user.username, user.name, user.last_name, user.email, user.is_verified, user.phone, user.avatar FROM post, user WHERE post.user_id = user.id AND post.id = %u", $this->db->insert_id());
        $post = $this->db->unique_query($sqlPost);

        $post['name'] = $this->getName($post);

        unset($post['username']);
        unset($post['last_name']);

        return $post;
    }

    public function delete($id, $user_id) {
        $sql = sprintf("DELETE FROM post WHERE id = %u AND user_id = %u", $this->db->sql_escape($id), $this->db->sql_escape($user_id));
        $this->db->query($sql);
        return $this->db->affected_rows();
    }

}