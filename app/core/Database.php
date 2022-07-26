<?php

namespace app\core;

use Exception;

class Database {

    private $connection;

    public function __construct() {
        $databaseConfig = App::$config['db'];

        $host = $databaseConfig['host'];
        $user = $databaseConfig['user'];
        $password = $databaseConfig['password'];
        $dbname = $databaseConfig['dbname'];
        $port = $databaseConfig['port'];
        $charset = $databaseConfig['charset'];

        $this->connection = mysqli_connect($host, $user, $password, $dbname, $port);
        $this->set_char($charset);
    }

    public function __destruct() {
        if ($this->connection) {
            mysqli_close($this->connection);
        }
    }

    private function set_char($charset) {
        $this->query("SET NAMES " . $charset);
    }

    public function query($resource) {
        return mysqli_query($this->connection, $resource);
    }

    public function unique_query($resource) {
        $result = $this->query($resource);
        $Return = $result->fetch_array(MYSQLI_ASSOC);
        $result->close();
        return $Return;
    }

    public function count_query($resource) {
        $result = $this->query($resource);
        list($Return) = $result->fetch_array(MYSQLI_NUM);
        $result->close();
        return $Return;
    }

    public function fetch_query($resource, $encode = []) {
        $result = $this->query($resource);
        $Return = [];
        while ($Data = $result->fetch_array(MYSQLI_ASSOC)) {
            foreach ($Data as $Key => $Store) {
                if (in_array($Key, $encode)) {
                    $Data[$Key] = base64_encode($Store);
                }
            }
            $Return[] = $Data;
        }
        $result->close();
        return $Return;
    }

    public function fetch_array($result) {
        return $result->fetch_array(MYSQLI_ASSOC);
    }

    public function fetch_num($result) {
        return $result->fetch_array(MYSQLI_NUM);
    }

    public function num_rows($query) {
        return $query->num_rows;
    }

    public function insert_id() {
        return $this->connection->insert_id;
    }

    public function sql_escape($string, $flag = false) {
        return ($flag === false) ? mysqli_real_escape_string($this->connection, $string) : addcslashes(mysqli_real_escape_string($this->connection, $string), '%_');
    }

    public function free_result($resource) {
        return $resource->close();
    }

    public function affected_rows() {
        return $this->connection->affected_rows;
    }

    public function fetch_fields($tablename) {
        return $this->query("SELECT * FROM " . $tablename . " LIMIT 1;")->fetch_fields();
    }

}