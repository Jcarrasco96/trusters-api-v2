<?php

namespace app\models;

use app\core\Model;
use Exception;

/**
 * STATUS OF DOWNLOADS
 * REQUESTED    - La descarga esta pendiente a ser aprobada por un Administrador.
 * PENDING      - La descarga ha sido aprobada y esta pendiente a descargarse.
 * DENIED       - La descarga fue denegada por un Administrador.
 * DOWNLOADED   - La descarga está lista.
 * FAILED       - La descarga falló.
 * ARCHIVED     - La descarga expiró y el archivo fue eliminado.
 */
class Download extends Model {

    public function index($page_size, $offset): array
    {
        $count = $this->db->count_query("SELECT COUNT(*) as count FROM download");
        $sql = "SELECT d.*, u.username as 'u_username', u.name as 'u_name', u.email as 'u_email' 
            FROM download as d 
            JOIN user as u ON d.user_id = u.id 
            ORDER BY d.created_at DESC 
            LIMIT $offset, $page_size";

        return [$this->db->fetch_query($sql), $count];
    }

    /**
     * @throws Exception
     */
    public function view($id): array
    {
        $id = $this->db->sql_escape($id);

        $sql = sprintf("SELECT d.*, u.username as 'u_username', u.name as 'u_name', u.email as 'u_email' FROM download as d JOIN user as u ON d.user_id = u.id WHERE d.user_id = u.id AND d.id = %u", $id);
        $post = $this->db->unique_query($sql);

        if (isset($post)) {
            return $post;
        }

        throw new Exception("Descarga no encontrada", 404);
    }

    public function owner($page_size, $offset, $user_id): array
    {
        $user_id = $this->db->sql_escape($user_id);
        $count = $this->db->count_query("SELECT COUNT(*) as count FROM download WHERE user_id = $user_id");

        $sql = "SELECT d.*, u.username as 'u_username', u.name as 'u_name', u.email as 'u_email' 
            FROM download as d 
            JOIN user as u ON d.user_id = u.id
            WHERE d.user_id = $user_id 
            ORDER BY d.created_at DESC 
            LIMIT $offset, $page_size";

        return [$this->db->fetch_query($sql), $count];
    }

    public function create(string $name, string $url, int $user_id): array
    {
        $name = $this->db->sql_escape($name);
        $url = $this->db->sql_escape($url);
        $user_id = $this->db->sql_escape($user_id);

        $sql = sprintf("INSERT INTO download (name, url, user_id) VALUES ('%s', '%s', '%s')", $name, $url, $user_id);
        $this->db->query($sql);

        $sql = "SELECT d.*, u.username as 'u_username', u.name as 'u_name', u.email as 'u_email' FROM download as d JOIN user as u ON d.user_id = u.id WHERE d.id = {$this->db->insert_id()}";

        return $this->db->unique_query($sql);
    }

    public function update(string $name, int $id, int $user_id): int|string
    {
        $name = $this->db->sql_escape($name);
        $id = $this->db->sql_escape($id);
        $user_id = $this->db->sql_escape($user_id);

        $sql = sprintf("UPDATE download SET name = '%s' WHERE id = %u AND user_id = '%s'", $name, $id, $user_id);
        $this->db->query($sql);

        return $this->db->affected_rows();
    }

    public function delete($id, $user_id): int|string
    {
        $id = $this->db->sql_escape($id);
        $user_id = $this->db->sql_escape($user_id);

        $sql = sprintf("DELETE FROM download WHERE id = %u AND user_id = %u", $id, $user_id);
        $this->db->query($sql);
        return $this->db->affected_rows();
    }

}