<?php

namespace app\core;

use mysqli;
use mysqli_result;

class Database
{

    private mysqli|false $connection;

    public function __construct()
    {
        $databaseConfig = App::$config['db'];

        $host = $databaseConfig['host'];
        $user = $databaseConfig['user'];
        $password = $databaseConfig['password'];
        $dbname = $databaseConfig['dbname'];
        $port = $databaseConfig['port'];
        $charset = $databaseConfig['charset'];

        $this->connection = mysqli_connect($host, $user, $password, $dbname, $port);

        $this->query("SET NAMES " . $charset);
    }

    public function __destruct()
    {
        if ($this->connection) {
            mysqli_close($this->connection);
        }
    }

    public function query(string $resource): mysqli_result|bool
    {
        return mysqli_query($this->connection, $resource);
    }

    public function unique_query(string $resource): false|array|null
    {
        return $this->query($resource)->fetch_assoc();
    }

    public function count_query(string $resource)
    {
        $data = $this->query($resource)->fetch_array(MYSQLI_NUM)[0];
        $this->query($resource)->close();
        return $data;
    }

    public function fetch_query(string $resource, $encode = []): array
    {
        $result = $this->query($resource);
        $arrRet = [];

        while ($assoc = $result->fetch_assoc()) {
            foreach ($encode as $Key) {
                if (isset($assoc[$Key])) {
                    $assoc[$Key] = base64_encode($assoc[$Key]);
                }
            }
            $arrRet[] = $assoc;
        }

        $result->close();
        return $arrRet;
    }

    public function fetch_array(mysqli_result $result): false|array|null
    {
        return $result->fetch_array(MYSQLI_ASSOC);
    }

    public function fetch_num(mysqli_result $result): false|array|null
    {
        return $result->fetch_array(MYSQLI_NUM);
    }

    public function num_rows(mysqli_result $result): int|string
    {
        return $result->num_rows;
    }

    public function insert_id(): int|string
    {
        return $this->connection->insert_id;
    }

    public function sql_escape($string, $flag = false): string
    {
        $escaped = mysqli_real_escape_string($this->connection, $string);
        return $flag ? addcslashes($escaped, '%_') : $escaped;
    }

    public function free_result(mysqli_result $result): void
    {
        $result->close();
    }

    public function affected_rows(): int|string
    {
        return $this->connection->affected_rows;
    }

    public function fetch_fields($tablename): array
    {
        return $this->query("SELECT * FROM " . $tablename . " LIMIT 1;")->fetch_fields();
    }

}