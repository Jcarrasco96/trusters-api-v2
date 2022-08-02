<?php

require_once 'vendor/autoload.php';

$config = require_once 'config/config.php';

date_default_timezone_set('America/Havana');

ignore_user_abort(true);
ini_set('display_errors', 0);
ini_set('error_log', 'error/error_' . date('Ymd') . '.log');

require_once 'exception_handler.php';

set_exception_handler('exception_handler');

$requestHeaders = array_change_key_case(getallheaders(), CASE_LOWER);
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

// auth
$app->post('/^auth\/login$/', 'login');
$app->post('/^auth\/register$/', 'register');
$app->post('/^auth\/logout$/', 'logout');

// posts
$app->get('/^posts$/', 'index');
$app->get('/^posts\/[0-9]+$/', 'view');
$app->get('/^posts\/owner$/', 'owner');
$app->get('/^posts\/[0-9]+\/comments$/', 'comments');
$app->post('/^posts$/', 'create');
$app->post('/^posts\/[0-9]+\/comments$/', 'comment');
//$app->put('/^posts\/[0-9]+$/', 'update');
$app->delete('/^posts\/[0-9]+$/', 'delete');

// user
$app->get('/^user$/', 'index');
$app->get('/^user\/current$/', 'current');
$app->get('/^user\/generate-avatar$/', 'generate-avatar');
//$app->post('/^user$/', 'create');
$app->post('/^user\/[0-9]+\/activate$/', 'activate');
$app->post('/^user\/[0-9]+\/desactivate$/', 'desactivate');
$app->post('/^user\/avatar$/', 'avatar');
$app->post('/^user\/wallet$/', 'wallet');
$app->post('/^user\/change-password$/', 'change-password');
$app->post('/^user\/[0-9]+\/set-role$/', 'set-role');
$app->post('/^user\/send-code$/', 'send-code');
$app->post('/^user\/verify$/', 'verify');
$app->put('/^user$/', 'update');

$app->run();
