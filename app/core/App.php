<?php

namespace app\core;

use Exception;

class App
{

    public static array $config = [];

    private array $routes = [];

    public function __construct($config = [])
    {
        self::$config = array_merge(self::$config, $config);

        define('ROOT', getcwd() . DIRECTORY_SEPARATOR);
        define('APP_PATH', ROOT . 'app' . DIRECTORY_SEPARATOR);
    }

    /**
     * @throws Exception
     */
    private function dispatch(): void
    {
        $path_info = $_SERVER['PATH_INFO'] ?? null;

        if (!$path_info) {
            throw new Exception("Acción no permitida", 405);
        }

        $path_info = trim($path_info, '/');
        $url = explode('/', $path_info);

        if (empty($url[0])) {
            throw new Exception("Acción no permitida", 405);
        }

        $method = strtolower($_SERVER['REQUEST_METHOD']);
        $routes = $this->routes[$method] ?? null;

        if (!$routes) {
            throw new Exception("Método no permitido", 400);
        }

        foreach ($routes as $regex => $action) {
            if (preg_match($regex, $path_info)) {
                $controller_name = 'app\\controllers\\' . ucfirst(array_shift($url)) . 'Controller';
                (new $controller_name)->createAction($action, $url);
                exit;
            }
        }

        throw new Exception("No se econtró el recurso solicitado.", 404);
    }

    public function run(): void
    {
        try {
            self::dispatch();
        } catch (Exception $e) {
            http_response_code($e->getCode());
            echo json_encode(['status' => $e->getCode(), 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }

    public function get($regex, $action): void
    {
        $this->routes['get'][$regex] = $action;
    }

    public function post($regex, $action): void
    {
        $this->routes['post'][$regex] = $action;
    }

    public function put($regex, $action): void
    {
        $this->routes['put'][$regex] = $action;
    }

    public function delete($regex, $action): void
    {
        $this->routes['delete'][$regex] = $action;
    }

}