<?php namespace scfr\WSBB\server;

use Ratchet\Server\IoServer;
use scfr\WSBB\server\EventServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Http\HttpServer;

    require dirname(__DIR__) . '/vendor/autoload.php';

    $ws = new WsServer(new EventServer());

    $server = IoServer::factory(
        new HttpServer($ws),
        8080
    );

    $server->run();