<?php

namespace app\core;

use Exception;
use ReflectionException;
use ReflectionMethod;

class Controller {

    public $dataJson;

    public function __construct() {
        // Obtener parámetros de la petición
        $parameters = file_get_contents('php://input');

        if ($parameters) {
            $dParams = json_decode($parameters, true);

            // Controlar posible error de parsing JSON
            if (json_last_error() != JSON_ERROR_NONE) {
                throw new Exception("Error interno en el servidor. Contacte al administrador", 500);
            }

            $this->dataJson = $dParams;
        }
    }

    /**
     * @ - autenticado
     * * - todos
     * ? - no autenticado
     *
     * @return array
     */
    public function behavior() {
        return [
            'access' => []
        ];
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
        $apiView = new JsonView();
        return $apiView->render($params);
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


