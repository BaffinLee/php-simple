<?php

class Route
{
    private static $default = 'index';

    public static function run ()
    {
        $controller = self::controller();
        $action = self::action().'Action';
        $class = Loader::controller($controller);
        if (!is_object($class)) throw new Exception("No controller: {$controller}");
        if (!method_exists($class, $action)) throw new Exception("No action: {$action} in {$controller}");
        try {
            call_user_func_array(array($class, $action), array());
        } catch (Exception $e) {
            $msg = "Error in controller: {$controller}, action: {$action}";
            Logger::error($msg.', '.$e->getMessage());
            throw new Exception($msg);
        }
    }

    public static function action ()
    {
        return isset($_GET['a']) ? $_GET['a'] : self::$default;
    }

    public static function controller ()
    {
        return isset($_GET['c']) ? $_GET['c'] : self::$default;
    }
}