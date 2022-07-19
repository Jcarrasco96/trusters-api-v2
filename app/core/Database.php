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
        $this->setChar($charset);
    }

    public function __destruct() {
        if ($this->connection) {
            mysqli_close($this->connection);
        }
    }

    private function setChar($charset) {
        $sql = "SET NAMES " . $charset;
        $this->query($sql);
    }

    public function query($resource) {
        $result = mysqli_query($this->connection, $resource);
        if (!$result) {
            throw new Exception("SQL Error: " . mysqli_error($this->connection), 500);
        }
        return $result;
    }

    public function uniquequery($resource) {
        $result = $this->query($resource);
        $Return = $result->fetch_array(MYSQLI_ASSOC);
        $result->close();
        return $Return;
    }

    public function countquery($resource) {
        $result = $this->query($resource);
        list($Return) = $result->fetch_array(MYSQLI_NUM);
        $result->close();
        return $Return;
    }

    public function fetchquery($resource, $encode = []) {
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

    public function GetInsertID() {
        return $this->connection->insert_id;
    }

    public function sql_escape($string, $flag = false) {
        return ($flag === false) ? mysqli_real_escape_string($this->connection, $string) : addcslashes(mysqli_real_escape_string($this->connection, $string), '%_');
    }

    public function str_correction($str) {
        return stripcslashes($str);
    }

    public function getVersion() {
        return mysqli_get_client_info();
    }

    public function getServerVersion() {
        return $this->connection->server_info;
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