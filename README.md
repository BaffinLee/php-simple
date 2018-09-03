# php-simple

Simple php mvc framework supports 5.3+

## Directory structure

```
├── config      # configs
├── controller  # controller
├── library     # core classes of framework
├── model       # models
├── public      # public files, like index.php
├── service     # services
├── template    # front-end templates, like index.html
├── core.php    # core entrance

```

*note*: 

- public dir should be exposed to public while any other dir should not.
- public/index.php should be the only entry of your app, do not expose index.html to user directly.
- if you want to support pathinfo mode or url rewrite, change library/Route.class.php to do so. It's not supported by default.

## Route

`$_GET['c']` is controller, `$_GET['a']` is action, default are index.

for example the url: `index.php?c=user&a=info`, means `infoAction` in `UserController` will handle this request.

## Autoload

core classes of framework will be loaded automaticly.

- `Loader`, load helper for controller, model, service
- `Config`, load config
- `Logger`, logger
- `Request`, helper funcs about request
- `Response`, helper funcs about response
- `View`, helper funcs about display
- `Model`, base class of model, include simple  `select`, `insert`, `update`, `delete` wrap
- `Controller`, base class of controller, empty by default
- `Service`, base class of service, empty by default
- `Route`, helper class for route
- `HttpRequest`, wrap for curl

## Loader

- `controller`

*params* `name` {string}, controller file name.

```php
$controller = Loader::controller('example');
$controller->exampleAction();
```

- `model`

*params* `name` {string}, model file name or table name.

```php
$userModel = Loader::model('user');
$userInfo = $userModel->selectOne('name, email', array('id' => 1));
```

- `service`

*params* `name` {string}, service file name.

```php
$uuidService = Loader::service('uuid');
$uuid = $uuidService->generate();
```

## Request

- `get`, get http get data by name
- `post`, get http post data by name
- `method`, get http method
- `ip`, get client ip
- `header` , get request header by name

## Response

- `header`, send http header
- `body`, send body data
- `end`, end request
- `json`, send json data
- `success`, send success json data
- `fail`, send fail json data
- `download`, send download file
- `redirect`, redirect client to other url
- `debug`, send cross origin debug headers and handle options request.

## Config

- `load`, load config file by name, the config file should return an array.
- `item`, read item in config file.

## Logger

- `info`, log message
- `debug`, log debug message
- `error`, log error message

> you can set debug level and log file path in config/log.php

## Session

- `get`, get session by name
- `set`, set session value
- `delete`, delete session key
- `clear`, clear all sessions
- `destroy`, destroy session

## View

- `display`, display an front-end html file

## Model

- `select`, select sql wrap
- `update`, update sql wrap
- `insert`, insert sql wrap
- `delete`, delete sql wrap
- `query`, query sql

## HttpRequest

- `get`, get request wrap
- `post`, post request wrap
- `request`, request by options
