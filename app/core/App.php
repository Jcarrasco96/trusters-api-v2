<?php

namespace app\core;

use Exception;

class App {

    public static $config = [];

    private $routes = [];

    public function __construct($config = []) {
        self::$config = array_merge(self::$config, $config);

        define('ROOT', getcwd() . DIRECTORY_SEPARATOR);
        define('APP_PATH', ROOT . 'app' . DIRECTORY_SEPARATOR);
        define('FONT_PATH', APP_PATH . 'fonts' . DIRECTORY_SEPARATOR);
    }

    private function dispatch() {
        if (!isset($_SERVER['PATH_INFO'])) {
            throw new Exception("Acción no permitida", 405);
        }

        $path_info = trim($_SERVER['PATH_INFO'], '/');
        $url = isset($path_info) ? explode('/', $path_info) : '/';

        if ($url == '/') {
            throw new Exception("Acción no permitida", 405);
        }

        $method = strtolower($_SERVER['REQUEST_METHOD']);

        if (!isset($this->routes[$method])) {
            throw new Exception("Metodo no permitido", 400);
        }

        foreach ($this->routes[$method] as $regex => $action) {
            if (preg_match($regex, $path_info)) {
                $requestedController = $url[0];
                $requestedParams = array_slice($url, 1);
                $requestedController = ucfirst($requestedController) . 'Controller';

                $controller_name = 'app\\controllers\\' . $requestedController;
                $controller = new $controller_name;
                $controller->createAction($action, $requestedParams);

                exit;
            }
        }

        throw new Exception("No se econtró el recurso solicitado.", 404);
    }

    public function run() {
        self::dispatch();
    }

    public function get($regex, $action) {
        $this->routes['get'][$regex] = $action;
    }

    public function post($regex, $action) {
        $this->routes['post'][$regex] = $action;
    }

    public function put($regex, $action) {
        $this->routes['put'][$regex] = $action;
    }

    public function delete($regex, $action) {
        $this->routes['delete'][$regex] = $action;
    }

}