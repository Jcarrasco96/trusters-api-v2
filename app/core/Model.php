<?php

namespace app\core;

class Model {

    public $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function __destruct() {
        $this->db = null;
    }

    protected function getName($record) {
        if (empty($record['name']) && empty($record['last_name'])) {
            return $record['username'];
        } else {
            return trim("{$record['name']} {$record['last_name']}");
        }
    }

}