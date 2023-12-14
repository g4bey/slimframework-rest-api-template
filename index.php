<?php

require __DIR__ . '/../vendor/autoload.php';

use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Tuupola\Middleware\JwtAuthenticationMiddleware;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use App\Controllers\{HomeController, UserController, EtapeController, LeaderboardController, ParcouresController, ChoixController};

// creating the app
// To add services to containers :
// https://www.slimframework.com/docs/v4/concepts/di.html
$container = new Container();
AppFactory::setContainer($container);
$app = AppFactory::create();

// enabling cors
$app->add(function ($request, $handler) {
  $response = $handler->handle($request);
  return $response
          ->withHeader('Access-Control-Allow-Origin', '*') // <- restric here if needed.
          ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
          ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
          ->withHeader('Access-Control-Allow-Credentials', 'true');
});

// middlewares
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);
// enabling jwt authentication
// $app->add(new Tuupola\Middleware\JwtAuthentication([
//   "path" => ["/api", "/admin"],
//   "ignore" => ["/api/token", "/admin/ping"],
//  "secret" => "supersecretkeyyoushouldnotcommittogithub"
// ]));

// the group "/api" is not necessary.
$app->group('/api', function (RouteCollectorProxy $group) {
  $group->get('/hello/{name}', function (Request $request, Response $response, $args) {
      $data = array('name' => $args['name']);
      $payload = json_encode($data);

      $response->getBody()->write($payload);
      return $response
      ->withHeader('Content-Type', 'application/json');
  });
});

// 404 redirection if no route found (must be last defined)
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
  throw new HttpNotFoundException($request);
});

$app->run();
