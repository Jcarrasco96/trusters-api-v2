<?php

return [
    'name' => 'DOWNLOAD API',
    'jwt' => [
        'serverkey' => '5f96af1223224844b3246539fe61ff22',
    ],
    'origins' => [
        'http://localhost',
        'http://127.0.0.1',
        'https://downloader.jcarrasco96.com',
    ],
    'db' => [
        'host' => 'localhost',
        'user' => 'root',
        'password' => '',
        'dbname' => 'test',
        'port' => '3306',
        'charset' => 'utf8',
    ],
    'roles' => [
        'admin' => 3,
        'normal' => 0,
    ],
    'env' => 'dev',
];