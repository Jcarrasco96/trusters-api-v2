<?php

return [
    'name' => 'TRUSTERS API',
    'jwt' => [
        'serverkey' => '5f96af12e5224844b3241209fe61ff22',
    ],
    'origins' => [
        'http://localhost',
        'http://127.0.0.1',
        'https://trusters.cmsagency.com.es',
    ],
    'db' => [
        'host' => 'localhost',
        'user' => 'root',
        'password' => '',
        'dbname' => 'trusters',
        'port' => '3306',
        'charset' => 'utf8',
    ],
    'roles' => [
        'admin' => 3,
        'normal' => 0,
    ],
    'env' => 'dev',
];