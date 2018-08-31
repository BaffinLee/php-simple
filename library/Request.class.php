<?php

class Request
{
    public static function init ()
    {
        self::getJsonInPost();
    }

    public static function get ($name, $default = null)
    {
        return isset($_GET[$name]) ? $_GET[$name] : $default;
    }

    public static function post ($name, $default = null)
    {
        return isset($_POST[$name]) ? $_POST[$name] : $default;
    }

    public static function method ()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function header ($name, $default = null)
    {
        $name = strtoupper(str_replace('-', '_', $name));
        if (isset($_SERVER[$name])) return $_SERVER[$name];
        $name = 'HTTP_'.$name;
        return isset($_SERVER[$name]) ? $_SERVER[$name] : $default;
    }

    private static function getJsonInPost ()
    {
        $isPost = self::method() === 'POST';
        $contentType = self::header('Content-Type');
        $isJson = $contentType && strpos(strtolower($contentType), 'application/json') !== false;
        if ($isPost && $isJson) {
            $json = file_get_contents('php://input');
            $json = json_decode(trim($json), true);
            if (is_array($json) && !empty($json)) {
                foreach ($json as $key => $val) {
                    $_POST[$key] = $val;
                }
            }
        }
    }
}