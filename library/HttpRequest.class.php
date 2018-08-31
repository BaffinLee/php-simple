<?php

/**
 * Class HttpRequest
-----------------------------------------
                   用法
-----------------------------------------

-----------------------------------------
                  通用请求
-----------------------------------------
$data = HttpRequest::request($options);

-----------------------------------------
                  GET请求
-----------------------------------------
$data = HttpRequest::get($options);
$data = HttpRequest::get($url, $params);

-----------------------------------------
                 POST请求
-----------------------------------------
$data = HttpRequest::post($options);
$data = HttpRequest::post($url, $data);

-----------------------------------------
                 其它请求
-----------------------------------------
$options['method'] = 'METHOD';

-----------------------------------------
                获取请求信息
-----------------------------------------
$info = HttpRequest::$lastInfo;

-----------------------------------------
                获取错误信息
-----------------------------------------
$err = HttpRequest::$lastError;

-----------------------------------------
           所有设置选项 $options
-----------------------------------------
$options = [
    'url' => 'http://example.com',              // 请求url
    'method' => 'get',                          // 请求http method, 默认get
    'method' => 'post',
    'timeout' => 10,                            // 超时，秒，默认10s
    'params' => [                               // URL中请求字段
        'id' => 1
    ],
    'data' => [                                 // post 数据
        'name' => 'value'
    ],
    'files' => [                                // post 文件
        'name' => [
            'name' => 'fileName',
            'path' => 'filePath,
            'type' => 'mineType',
        ]
    ],
    'header' => [                               // http header
        'Content-Type' => 'text/html'
    ],
    'proxy' => 'http://example2.com:8080',      // 代理
    'proxy' => 'socks5://example2.com:8080',
    'referer' => 'http://example3.com',         // referer
    'cookie' => [                               // cookie
        'name' => 'value'
    ],
    'userAgent' => 'Mozilla/5.0 Chrome/61',     // 用户代理字段
    'noCache' => true,                          // 不使用缓存
    'auth' => 'username:password',              // http认证
    'checkSsl' => true                          // 检查ssl证书
];

*/

class HttpRequest
{
    private static $defaults = array(
        CURLOPT_MAXREDIRS => 100,
        CURLOPT_RETURNTRANSFER =>  true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HEADER => false,
    );

    private static $optionsMap = array(
        'url' => CURLOPT_URL,
        'header' => CURLOPT_HTTPHEADER,
        'proxy' => CURLOPT_PROXY,
        'timeout' => CURLOPT_TIMEOUT,
        'checkSsl' => array(
            CURLOPT_SSL_VERIFYPEER,
            CURLOPT_SSL_VERIFYHOST,
        ),
        'referer' => CURLOPT_REFERER,
        'cookie' => CURLOPT_COOKIE,
        'auth' => CURLOPT_USERPWD,
        'userAgent' => CURLOPT_USERAGENT,
        'noCache' => array(
            CURLOPT_FORBID_REUSE,
            CURLOPT_FRESH_CONNECT,
        ),
        'post' => CURLOPT_POST,
        'method' => CURLOPT_CUSTOMREQUEST,
        'data' => CURLOPT_POSTFIELDS,
    );

    public static $lastInfo = null;
    public static $lastError = null;
    public static $lastResult = null;

    public static function request($options)
    {
        Logger::debug('request: '.json_encode($options));

        $options = self::processOptions($options);

        $ch = curl_init();

        curl_setopt_array($ch, (self::$defaults + $options));

        self::$lastResult = curl_exec($ch);
        self::$lastError = array(
            'code' => curl_errno($ch),
            'message' => curl_error($ch)
        );
        self::$lastInfo = curl_getinfo($ch);

        if (self::$lastError['code']) {
            Logger::error(sprintf(
                'request error: code %s, msg %s, info %s',
                self::$lastError['code'],
                self::$lastError['message'],
                json_encode(self::$lastInfo)
            ));
        }

        curl_close($ch);

        return self::$lastResult;
    }

    public static function get($urlOrOptions, $paramsOrNull = array())
    {
        if (is_array($urlOrOptions)) {
            return self::request($urlOrOptions);
        }

        $options = array(
            'url' => $urlOrOptions,
            'params' => $paramsOrNull
        );

        return self::request($options);
    }

    public static function post($urlOrOptions, $dataOrNull = array())
    {
        if (is_array($urlOrOptions)) {
            $urlOrOptions['method'] = 'POST';
            return self::request($urlOrOptions);
        }

        $options = array(
            'url' => $urlOrOptions,
            'method' => 'POST',
            'data' => $dataOrNull
        );

        return self::request($options);
    }

    private static function processOptions($options)
    {
        $res = array();

        if (isset($options['method'])) {
            $method = strtoupper($options['method']);
            switch ($method) {
                case 'GET':
                    unset($options['method']);
                    break;
                case 'POST':
                    $options['post'] = true;
                    unset($options['method']);
                    break;
                default:
                    $options['method'] = $method;
                    break;
            }
        }

        if (!empty($options['params'])) {
            $params = http_build_query($options['params']);
            $url = rtrim(rtrim($options['url'], '&'), '?');
            if (preg_match('/\?\S/', $url)) {
                $options['url'] = $url.'&'.$params;
            } else {
                $options['url'] = $url.'?'.$params;
            }
        }

        if (isset($options['files']) && is_array($options['files'])) {
            if (!isset($options['data'])) {
                $options['data'] = array();
            }
            foreach ($options['files'] as $key => $value) {
                $options['data'][$key] = curl_file_create($value['path'], $value['type'], $value['name']);
            }
            unset($options['files']);
        }

        if (isset($options['cookie']) && is_array($options['cookie'])) {
            $cookie = '';
            foreach ($options['cookie'] as $key => $value) {
                $cookie .= sprintf(';%s=%s', $key, $value);
            }
            $cookie = ltrim($cookie, ';');
            $options['cookie'] = $cookie;
        }

        if (isset($options['header']) && is_array($options['header'])) {
            $header = array();
            foreach ($options['header'] as $key => $value) {
                $header[] = sprintf('%s: %s', $key, $value);
            }
            $options['header'] = $header;
        }

        foreach (self::$optionsMap as $key => $value) {
            if (isset($options[$key])) {
                if (is_array($value)) {
                    foreach ($value as $id) {
                        $res[$id] = $options[$key];
                    }
                } else {
                    $res[$value] = $options[$key];
                }
            }
        }

        return $res;
    }
}
