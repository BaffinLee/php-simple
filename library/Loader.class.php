<?php

class Loader
{
    private static $store = array(
        'model' => array(),
        'service' => array(),
        'controller' => array()
    );

    public static function load ($type, $name)
    {
        $cname = ucfirst($name);
        if (strpos($name, '.') !== false || strpos($name, '/') !== false) {
            throw new Exception("Invalid name: {$name}");
        }
        if (!isset(self::$store[$type])) throw new Exception("Not support type: {$type}");
        if (!isset(self::$store[$type][$name])) {
            switch ($type) {
                case 'model':
                    $path = MODEL_PATH.'/'.$cname.'.php';
                    $class = $cname.'Model';
                    break;
                case 'service':
                    $path = SERVICE_PATH.'/'.$cname.'.php';
                    $class = $cname.'Service';
                    break;
                case 'controller':
                    $path = CONTROLLER_PATH.'/'.$cname.'.php';
                    $class = $cname.'Controller';
                    break;
            }
            if (!file_exists($path)) {
                if ($type == 'model') {
                    self::$store[$type][$name] = new Model($name);
                    return self::$store[$type][$name];
                }
                throw new Exception("No file: {$type} {$name}");
            }
            require_once($path);
            if (!class_exists($class)) throw new Exception("Wrong format: {$class}");
            self::$store[$type][$name] = new $class();
        }
        return self::$store[$type][$name];
    }

    /**
     * @return Model
     */
    public static function model ($name)
    {
        return self::load('model', $name);
    }

    /**
     * @return Service
     */
    public static function service ($name)
    {
        return self::load('service', $name);
    }

    /**
     * @return Controller
     */
    public static function controller ($name)
    {
        return self::load('controller', $name);
    }
}