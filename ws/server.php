<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/CourseSocket.php';
require __DIR__ . '/JwtHelper.php';

use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Server\IoServer;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new CourseSocket()
        )
    ),
    8080
);

echo "WebSocket server started on port 8080\n";
$server->run();
