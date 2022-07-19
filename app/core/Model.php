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

}