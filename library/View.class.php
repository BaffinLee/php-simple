<?php

class View
{
    public static function display ($name = 'index')
    {
        Response::header('Content-Type', 'text/html; charset=utf-8');
        Response::header('Cache-Control', 'no-store, no-cache, must-revalidate');
        readfile(TEMPLATE_PATH.'/'.$name.'.html');
    }
}