<?php

namespace app\core;

use Exception;
use ReflectionException;
use ReflectionMethod;

class Controller {

    public $dataJson;

    public function __construct() {
        $parameters = file_get_contents('php://input'); // Obtener parámetros de la petición

        if ($parameters) {
            $dParams = json_decode($parameters, true);

            if (json_last_error() != JSON_ERROR_NONE) { // Controlar posible error de parsing JSON
                throw new Exception("Error interno en el servidor. Contacte al administrador con este codigo: JSON" . json_last_error(), 500);
            }

            $this->dataJson = $dParams;
        }
    }

    public function createAction($methodName, $params = []) {
        $methodNameNormalized = $this->normalizeAction($methodName);

        try {
            $method = new ReflectionMethod($this, $methodNameNormalized);
            if ($method->isPublic() && $method->getName() === $methodNameNormalized) {
                echo $this->render(call_user_func_array([$this, $methodNameNormalized], $params));
            }
            return [];
        } catch (ReflectionException $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }
    }

    private function render($params = []) {
        if (isset($params["status"])) {
            http_response_code($params["status"]);
        }

        header('Content-Type: application/json; charset=utf8');

        $jsonResponse = json_encode($params, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

        if (json_last_error() != JSON_ERROR_NONE) {
            throw new Exception("Error interno en el servidor. Contacte al administrador", 500);
        }

        return $jsonResponse;
    }

    private function normalizeAction($methodName) {
        $actionParts = explode('-', $methodName);
        $action = array_shift($actionParts);

        foreach ($actionParts as $part) {
            $action .= ucfirst($part);
        }

        return $action;
    }

}


