<?php

define('ROOT_PATH', __DIR__);
define('LIB_PATH', ROOT_PATH.'/library');
define('CONFIG_PATH', ROOT_PATH.'/config');
define('CONTROLLER_PATH', ROOT_PATH.'/controller');
define('MODEL_PATH', ROOT_PATH.'/model');
define('SERVICE_PATH', ROOT_PATH.'/service');
define('PUBLIC_PATH', ROOT_PATH.'/public');
define('TEMPLATE_PATH', ROOT_PATH.'/template');
define('IS_WIN', stripos(PHP_OS, 'WIN') !== false);
define('IS_DEBUG', getenv('DEBUG') === 'on');

ini_set('date.timezone', 'Asia/Shanghai');
ini_set('display_errors', IS_DEBUG ? '1' : '0');
error_reporting(IS_DEBUG ? E_ALL : E_ERROR);

spl_autoload_register(function ($class) {
    $path = LIB_PATH.'/'.$class.'.class.php';
    require_once($path);
});

Request::init();
Response::init();
Session::init();

try {
    Route::run();
} catch (Exception $e) {
    Response::fail($e->getMessage(), 500);
}

register_shutdown_function(function () {
    Response::end();
    Logger::flush();
});