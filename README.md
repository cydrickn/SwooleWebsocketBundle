# Swoole Websocket Bundle

A bundle for websocket using swoole

## Requirements

- [Swoole](https://www.swoole.com/) or [OpenSwoole](https://openswoole.com/)
- PHP >= 8.1
- Symfony >= 6.1

## Intallation

Add the package
```shell
composer require cydrickn/swoole-websocket-bundle
```

## Run the server

```shell
php ./bin/console websocket:server
```

## Commands

### websocket:server

Run the websocket server

| Options | Details                | Default   |
|---------|------------------------|-----------|
| host    | The host of the server | 127.0.0.1 |
| port    | The port of the server | 8000      |

## Events

This bundle will have an event where you can listen

### \Cydrickn\SwooleWebsocketBundle\Event\OpenEvent

This event will be trigger once a client have been connected to the server.

### \Cydrickn\SwooleWebsocketBundle\Event\MessageEvent

This event will be trigger once the client send a message

### \Cydrickn\SwooleWebsocketBundle\Event\CloseEvent

This event will be trigger once a client have been disconnected

## Client connection using Javascript

For the basic way on connecting to websocket you can use the Browser Websocket

```js
const socket = new WebSocket('ws://localhost:8080');

// Connection opened
socket.addEventListener('open', (event) => {
    socket.send('Hello Server!');
});

// Listen for messages
socket.addEventListener('message', (event) => {
    console.log('Message from server ', event.data);
});

```

## Use it in runtime

Using the command is fine, but if you happen you want to serve this using symfony runtime you can do so.

1. First you need to include symfony/runtime
   ```shell
   composer require symfony/runtime
   ```
2. Next run your `public/index.php` with env APP_RUNTIME
   ```shell
   APP_RUNTIME=\\Cydrickn\\SwooleWebsocketBundle\\Runtime\\Runtime php ./public/index.php
   ```

#### Runtime Configuration

For runtime configuration you can add it to your `public/index.php`

| Key        | Details                                                                                                         | Default             |
|------------|-----------------------------------------------------------------------------------------------------------------|---------------------|
| host       | The host of the server                                                                                          | 127.0.0.1           |
| port       | The port of the server                                                                                          | 8000                |
| mode       | The mode for server                                                                                             | 2 / SWOOLE_PROCESS  |
| sock_type  | The socket type for server                                                                                      | 1 / SWOOLE_SOCK_TCP |
| settings   | The setting is base on [swoole configuration ](https://openswoole.com/docs/modules/swoole-server/configuration) | [] / Empty Array    |
| serve_http | Include to serve HTTP                                                                                           | false               |

**Example**:
```php
#!/usr/bin/env php
<?php

use App\Kernel;

$_SERVER['APP_RUNTIME_OPTIONS'] = [
    'host' => '0.0.0.0',
    'port' => 8000,
    'mode' => SWOOLE_PROCESS,
    'settings' => [
        \Swoole\Constant::OPTION_WORKER_NUM => swoole_cpu_num() * 2,
        \Swoole\Constant::OPTION_ENABLE_STATIC_HANDLER => true,
        \Swoole\Constant::OPTION_DOCUMENT_ROOT => dirname(__DIR__).'/public'
    ],
];

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
```

> Also instead of having to server for websocket and http, you can just enable the `Serve HTTP`, by setting the `serve_http` to true

## Hot reloading

We already include a hor reloading for this bundle.
To enable it you need to first require [cydrickn/php-watcher](https://packagist.org/packages/cydrickn/php-watcher)

Hot reloading is only available if you are using runtime

```shell
composer require cydrickn/php-watcher
```

Now add to your `APP_RUNTIME_OPTIONS` the `hotReload`
The `basePath` should be your project folder

```php
$_SERVER['APP_RUNTIME_OPTIONS'] = [
    'host' => '0.0.0.0',
    'port' => 8000,
    'mode' => SWOOLE_PROCESS,
    'hotReload'=> [
        'enabled' => true,
        'basePath' => dirname(__DIR__),
    ],
    'settings' => [
        \Swoole\Constant::OPTION_WORKER_NUM => swoole_cpu_num() * 2,
        \Swoole\Constant::OPTION_ENABLE_STATIC_HANDLER => true,
    ],
];
```

## Socket IO

In this bundle you can also enable using socket.io implementation, to enable it first you need to install
[cydrickn/socketio](https://packagist.org/packages/cydrickn/socketio)

This implementation is only available if you are using runtime

```shell
composer require cydrickn/socketio
```

Now add to your `APP_RUNTIME_OPTIONS` set the `socketio` to `true`

```php
$_SERVER['APP_RUNTIME_OPTIONS'] = [
    'socketio' => true,
    'host' => '0.0.0.0',
    'port' => 8000,
    'mode' => SWOOLE_PROCESS,
    'settings' => [
        \Swoole\Constant::OPTION_WORKER_NUM => swoole_cpu_num() * 2,
        \Swoole\Constant::OPTION_ENABLE_STATIC_HANDLER => true,
    ],
];
```

### Adding a route to socket io

For pure socket io you will do this
```php
$server->on('connection', function (\Cydrickn\SocketIO\Socket $socket) {
    $socket->emit('hello', 'world');
});

$server->on('chat', function (\Cydrickn\SocketIO\Socket $socket, $message) {
    $socket->broadcast()->emit('chat', $message);
});
```

You will use the Attribute Route of this bundle `Cydrickn\SwooleWebsocketBundle\Attribute\RouteAttribute`
In any of your service

```php
<?php
namespace App\Controller;

use Cydrickn\SwooleWebsocketBundle\Attribute\RouteAttribute;

class SocketController
{
   #[RouteAttribute(path: 'connection')]
   public function connection(\Cydrickn\SocketIO\Socket $socket)
   {
      $socket->emit('hello', 'world');
   }
   
   #[RouteAttribute(path: 'chat')]
   public function anotherMethod(\Cydrickn\SocketIO\Socket $socket, $message)
   {
      $socket->broadcast()->emit('chat', $message);
   }
}
```
