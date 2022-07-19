<?php

require_once 'vendor/autoload.php';

$config = require_once 'config/config.php';

date_default_timezone_set('America/Havana');

ignore_user_abort(true);
ini_set('display_errors', 0);
ini_set('error_log', 'error/error_' . date('Ymd') . '.log');

require_once 'exception_handler.php';

set_exception_handler('exception_handler');

$requestHeaders = getallheaders();
if (isset($requestHeaders['Origin'])) {
    if (in_array($requestHeaders['Origin'], $config['origins'], true)) {
        header('Access-Control-Allow-Origin: ' . $requestHeaders['Origin']);
    }
}

header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');

if (strtolower($_SERVER['REQUEST_METHOD']) == 'options') {
    die();
}

$app = new app\core\App($config);

//$app->get('/^auth\/index$/', 'index');

$app->post('/^auth\/login$/', 'login');
$app->post('/^auth\/register$/', 'register');

$app->get('/^user\/index$/', 'index');

$app->run();
