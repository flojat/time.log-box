<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

$app = new \Slim\App;
$app->get('/login', function (Request $request, Response $response) {
    $user = $request->getAttribute('user');  
    $pass = $request->getAttribute('password');     
    $response->getBody()->write("Hello, $name identified by $pass");
    return $response;
});
$app->run();
