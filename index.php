<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;

require __DIR__.'/vendor/autoload.php';

$app = AppFactory::create();

$app->get('/', function (ServerRequestInterface $request, ResponseInterface $response) {
    $response->getBody()->write('Hello on main page');
    return $response;
});

$app->get('/login', function (ServerRequestInterface $request, ResponseInterface $response) {
    $response->getBody()->write('Welcome to login page');
    return $response;
});

$app->run();

