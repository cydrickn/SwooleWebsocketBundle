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
| port    | The port of the server | 8080      |

## Events

This bundle will have an event where you can listen

### websocket:open

This event will be trigger once a client have been connected to the server.

Instead of using the event name you can listen / subscribe to event class **\Cydrickn\SwooleWebsocketBundle\Event\OpenEvent**

### websocket:message

This event will be trigger once the client send a message

Instead of using the event name you can listen / subscribe to event class **\Cydrickn\SwooleWebsocketBundle\Event\MessageEvent**

### websocket:close

This event will be trigger once a client have been disconnected

Instead of using the event name you can listen / subscribe to event class **\Cydrickn\SwooleWebsocketBundle\Event\CloseEvent**

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

## TODO

- [ ] Add Routing of message
- [ ] Support Symfony Runtime
- [ ] Support PHP >= 7
- [ ] Support Symfony >= 4
- [ ] Create javascript library
- [ ] Supports socket.io javascript
