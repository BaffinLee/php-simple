<?php

class Session
{
    public static function init ()
    {
        $config = Config::load('session');
        ini_set('session.gc_maxlifetime', $config['timeout']);
        ini_set('session.cookie_lifetime', $config['timeout']);
        ini_set('session.cookie_httponly', true);
        session_start();
    }

    public static function get ($name, $default = null)
    {
        return isset($_SESSION[$name]) ? $_SESSION[$name] : $default;
    }

    public static function set ($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    public static function delete ($name)
    {
        unset($_SESSION[$name]);
    }

    public static function clear ()
    {
        session_unset();
    }

    public static function destroy ()
    {
        session_destroy();
    }
}