<?php

namespace app\core;

use Exception;
use ReflectionException;
use ReflectionMethod;

class Controller
{

    public mixed $dataJson;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        if ($parameters = file_get_contents('php://input')) {
            $this->dataJson = json_decode($parameters, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Error interno en el servidor. Contacte al administrador con este código: JSON" . json_last_error(), 500);
            }
        }
    }

    /**
     * @throws Exception
     */
    public function createAction($methodName, $params = []): array
    {
        $methodNameNormalized = $this->normalizeAction($methodName);

        try {
            $method = new ReflectionMethod($this, $methodNameNormalized);
            if ($method->isPublic()) {
                echo $this->render($method->invokeArgs($this, $params));
            }
        } catch (ReflectionException $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }

        return [];
    }

    /**
     * @throws Exception
     */
    private function render($params = []): false|string
    {
        if (isset($params["status_code"])) {
            http_response_code($params["status_code"]);
        }

        header('Content-Type: application/json; charset=utf8');

        $jsonResponse = json_encode($params, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

        return $jsonResponse !== false ? $jsonResponse : throw new Exception("Error interno en el servidor. Contacte al administrador", 500);
    }

    private function normalizeAction($methodName): ?string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $methodName))));
    }

}


