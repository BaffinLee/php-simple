<?php

class Config
{
    private static $default = 'config';
    private static $store = array();

    public static function load ($name)
    {
        if (!isset(self::$store[$name])) {
            self::$store[$name] = require(CONFIG_PATH.'/'.$name.'.php');
        }
        return self::$store[$name];
    }

    public static function item ($name, $default = null)
    {
        $config = self::load(self::$default);
        return isset($config[$name]) ? $config[$name] : $default;
    }
}