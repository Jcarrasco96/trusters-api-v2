<?php

require_once 'vendor/autoload.php';

date_default_timezone_set('America/Havana');

ignore_user_abort(true);
ini_set('display_errors', 0);
ini_set('error_log', 'error/error_' . date('Ymd') . '.log');

require_once 'exception_handler.php';

set_exception_handler('exception_handler');

