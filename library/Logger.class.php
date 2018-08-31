<?php

class Logger
{
    private static $handle = null;
    private static $level = 'error';
    private static $levelMap = array(
        'info' => 1,
        'debug' => 2,
        'error' => 3
    );

    public static function init ()
    {
        if (self::$handle) return;
        $config = Config::load('log');
        self::$level = $config['level'];
        self::$handle = fopen($config['file'], 'a');
    }

    public static function debug ($msg)
    {
        self::log('debug', $msg);
    }

    public static function error ($msg)
    {
        self::log('error', $msg);
    }

    public static function info ($msg)
    {
        self::log('info', $msg);
    }

    public static function flush ()
    {
        if (!self::$handle) return;
        fclose(self::$handle);
    }

    private static function log ($type, $msg)
    {
        if (self::$levelMap[$type] < self::$levelMap[self::$level]) return;
        if (!self::$handle) self::init();
        $time = date('Y-m-d H:i:s');
        $msg = "{$time} [{$type}]: {$msg}\n";
        fwrite(self::$handle, $msg);
    }
}