<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use MyApp\Chat;

// include database and object files
include_once 'config/database.php';

// instantiate database and group object
$database = new Database();
$db = $database->getConnection();

require dirname(__DIR__) . '/vendor/autoload.php';

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat($db)
        )
    ),
    8080
);

$server->run();