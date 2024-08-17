<?php

require_once 'app.php';

$config = require_once 'config/config.php';

$app = new app\core\App($config);

$requestHeaders = array_change_key_case(getallheaders(), CASE_LOWER);
if (isset($requestHeaders['Origin'])) {
    if (in_array($requestHeaders['Origin'], $config['origins'], true)) {
        header('Access-Control-Allow-Origin: ' . $requestHeaders['Origin']);
    }
}

header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");

if (strtolower($_SERVER['REQUEST_METHOD']) == 'options') {
    die();
}

// auth
$app->post('/^auth\/login$/', 'login');
$app->post('/^auth\/register$/', 'register');
$app->post('/^auth\/logout$/', 'logout');

// download
$app->get('/^download$/', 'index');
$app->get('/^download\/[0-9]+$/', 'view');
$app->get('/^download\/owner$/', 'owner');
$app->post('/^download$/', 'create');
$app->put('/^download\/[0-9]+$/', 'update');
$app->delete('/^download\/[0-9]+$/', 'delete');

// user
$app->get('/^user$/', 'index');
$app->get('/^user\/current$/', 'current');
$app->post('/^user\/[0-9]+\/activate$/', 'activate');
$app->post('/^user\/[0-9]+\/desactivate$/', 'desactivate');
$app->post('/^user\/change-password$/', 'change-password');
$app->post('/^user\/[0-9]+\/set-role$/', 'set-role');
$app->post('/^user\/send-code$/', 'send-code');
$app->post('/^user\/verify$/', 'verify');
$app->put('/^user$/', 'update');

$app->run();
