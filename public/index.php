<?php

declare(strict_types=1);

use App\Infrastructure\Http\CallableResolver;
use DI\ContainerBuilder;
use Slim\App;
use Slim\Psr7\Factory\ResponseFactory;

require __DIR__ . '/../vendor/autoload.php';

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/../config/container.php');
$container = $containerBuilder->build();

$app = new App(new ResponseFactory(), $container, new CallableResolver());

/** @var (callable(App): void) $middleware */
$middleware = require __DIR__ . '/../config/middleware.php';
$middleware($app);

/** @var (callable(App): void) $routes */
$routes = require __DIR__ . '/../config/routes.php';
$routes($app);

$app->run();
