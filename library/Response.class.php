<?php

class Response
{
    private static $header = array();
    private static $ended = false;

    public static function init ()
    {
        ob_start();
        if (IS_DEBUG) self::debug();
    }

    public static function header ($name = null, $value = null)
    {
        if ($name === null) return self::$header;
        else if ($value === null) return self::$header[$name];
        else return self::$header[$name] = $value;
    }

    public static function body ($body)
    {
        echo $body;
    }

    public static function end ()
    {
        if (self::$ended) return;
        self::$ended = true;
        self::sendHeader();
        self::sendBody();
        exit;
    }

    public static function json ($data = null, $msg = 'ok', $code = 200)
    {
        self::header('Content-Type', 'application/json');
        self::header('Cache-Control', 'no-store, no-cache, must-revalidate');
        echo json_encode(array(
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ));
        self::end();
    }

    public static function success ($data = null)
    {
        self::json($data);
    }

    public static function fail ($msg = 'fail', $code = 500)
    {
        self::json(null, $msg, $code);
    }

    public static function download ($name, $type = 'application/octet-stream')
    {
        self::header('Content-Type', $type);
        self::header('Content-Disposition','attachment; filename='.$name);
    }

    public static function redirect ($url)
    {
        self::header('Location', $url);
    }

    public static function sendHeader ()
    {
        foreach (self::$header as $key => $val) {
            header("{$key}: {$val}");
        }
    }

    public static function debug ()
    {
        self::header('Access-Control-Allow-Origin', rtrim(Request::header('referer'), '/'));
        self::header('Access-Control-Allow-Headers', 'X-TOKEN, X-Requested-With, Content-Type, Accept, Origin, Authorization, X-Requested-By, X-XSRF-TOKEN, X-CSRF-TOKEN');
        self::header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        self::header('Access-Control-Allow-Credentials', 'true');
        self::header('Access-Control-Max-Age', '17280000');
        if (Request::method() === 'OPTIONS') self::end();
    }

    public static function sendBody ()
    {
        ob_end_flush();
    }
}