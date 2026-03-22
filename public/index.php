<?php

declare(strict_types=1);

use DI\Bridge\Slim\Bridge;
use DI\ContainerBuilder;
use Slim\App;

require __DIR__ . '/../vendor/autoload.php';

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/../config/container.php');
$container = $containerBuilder->build();

$app = Bridge::create($container);

/** @var (callable(App): void) $middleware */
$middleware = require __DIR__ . '/../config/middleware.php';
$middleware($app);

/** @var (callable(App): void) $routes */
$routes = require __DIR__ . '/../config/routes.php';
$routes($app);

$app->run();
