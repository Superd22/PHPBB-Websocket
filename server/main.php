<?php namespace scfr\WSBB\server;

use Ratchet\Server\IoServer;
use scfr\WSBB\server\EventServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Http\HttpServer;

    require dirname(__DIR__) . '/vendor/autoload.php';

    $ev = new EventServer();
    $loop   = \React\EventLoop\Factory::create();

    $context = new \React\ZMQ\Context($loop);

    $pull = $context->getSocket(\ZMQ::SOCKET_PULL);
    $pull->bind('tcp://127.0.0.1:5555');
    $pull->on('message', array($ev, 'onPhpbbPacket') );
    
    $webSock = new \React\Socket\Server("0.0.0.0:8080", $loop);
    //$webSock->listen(8080, '0.0.0.0');

    $wsServer = new WsServer($ev);
    $wsServer->enableKeepAlive($loop);


    $webServer  = new \Ratchet\Server\IoServer(
        new HttpServer(
            $wsServer
        ),
        $webSock
    );

    $loop->run();