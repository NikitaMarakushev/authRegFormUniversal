<?php

use App\Authorization;
use App\AuthorizationException;
use App\Database;
use App\Session;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require __DIR__.'/vendor/autoload.php';

$loader = new FilesystemLoader('templates');
$twig = new Environment($loader);

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

$session = new Session();
/**
 * @param ServerRequestInterface $request
 * @param RequestHandlerInterface $handler
 * @return ResponseInterface
 */
$sessionMiddleWare = function (ServerRequestInterface $request, RequestHandlerInterface $handler) use ($session)
{
    $session->start();
    $response = $handler->handle($request);
    $session->save();

    return $response;

};


/** @var TYPE_NAME $app */
$app->add($sessionMiddleWare);

$config = include_once  'config/database.php';
$dsn = $config['dsn'];
$username = $config['username'];
$password = $config['password'];

/** @var TYPE_NAME $database */
$database = new Database($dsn, $username, $password);

/** @var TYPE_NAME $authorization */
$authorization = new Authorization($database, $session);

$app->get('/', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $session) {
    $body = $twig->render('index.twig', [
        'user' => $session->getData('user')
    ]);

    $response->getBody()->write($body);

    return $response;
});

$app->get('/login', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $session) {
    $body = $twig->render('index.twig', [
        'message' => $session->flush('message'),
        'form' => $session->flush('form'),
    ]);

    $response->getBody()->write($body);

    return $response;
});

$app->post('/login-post', function (ServerRequestInterface $request, ResponseInterface $response) use($authorization, $session) {
    $params = (array) $request->getParsedBody();

    try {
        $authorization->login($params['email'], $params['password']);
    } catch(AuthorizationException $exception) {
        $session->setData('message', $exception->getMessage());
        $session->setData('form', $params);

        return $response->withHeader('Location', '/login')->withStatus(302);
    }

    return $response->withHeader('Location', '/')->withStatus(302);
});

$app->get('/register', function (ServerRequestInterface $request, ResponseInterface $response) use ($twig, $session) {
    $body = $twig->render('register.twig', [
        'message' => $session->flush('message'),
        'form' => $session->flush('form')
    ]);

    $response->getBody()->write($body);

    return $response;
});

$app->post('/register-post', function (ServerRequestInterface $request, ResponseInterface $response) use($session, $authorization) {
    $params = (array)$request->getParsedBody();

    try {
        $authorization->register($params);
    }catch(AuthorizationException $exception) {
        $session->setData('message', $exception->getMessage());
        $session->setData('form', $params);

        return $response->withHeader('Location', '/register')->withStatus(302);
    }
    return $response->withHeader('Location', '/')->withStatus(302);

});

$app->get('/logout', function (ServerRequestInterface $request, ResponseInterface $response) use($session) {
    $session->setData('user', null);
    return $response->withHeader('Location', '/')->withStatus(302);
});


$app->run();

