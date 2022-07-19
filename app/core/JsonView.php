<?php

namespace app\core;

use Exception;

class JsonView {

    public function render($body) {
        if (isset($body["status"])) {
            http_response_code($body["status"]);
        }

        header('Content-Type: application/json; charset=utf8');

        $jsonResponse = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

        if (json_last_error() != JSON_ERROR_NONE) {
            throw new Exception("Error interno en el servidor. Contacte al administrador", 500);
        }

        return $jsonResponse;
    }

}