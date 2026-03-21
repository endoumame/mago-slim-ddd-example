<?php

declare(strict_types=1);

use App\Infrastructure\Http\Middleware\ErrorHandlerMiddleware;
use App\Infrastructure\Http\Middleware\JsonBodyParserMiddleware;
use Slim\App;

return function (App $app): void {
    $app->addBodyParsingMiddleware();
    $app->add(new JsonBodyParserMiddleware());
    $app->add(new ErrorHandlerMiddleware());
    $app->addRoutingMiddleware();
};
