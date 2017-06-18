<?php namespace scfr\WSBB\server;

use Ratchet\Server\IoServer;
use scfr\WSBB\server\EventServer;

    require dirname(__DIR__) . '/vendor/autoload.php';

    $server = IoServer::factory(
        new EventServer(),
        8080
    );

    $server->run();