<?php

declare(strict_types=1);

use App\Infrastructure\Http\Controller\TaskController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app): void {
    /** @var TaskController $c */
    $c = $app->getContainer()?->get(TaskController::class);

    $app->group('/api', function (RouteCollectorProxy $group) use ($c): void {
        $group->get('/tasks', $c->list(...));
        $group->post('/tasks', $c->create(...));
        $group->get('/tasks/{id}', fn(ServerRequestInterface $request): ResponseInterface => $c->get((string) $request->getAttribute(
            'id',
        )));
        $group->put('/tasks/{id}', fn(ServerRequestInterface $request): ResponseInterface => $c->update(
            $request,
            (string) $request->getAttribute('id'),
        ));
        $group->delete('/tasks/{id}', fn(ServerRequestInterface $request): ResponseInterface => $c->delete((string) $request->getAttribute(
            'id',
        )));
        $group->patch('/tasks/{id}/status', fn(ServerRequestInterface $request): ResponseInterface => $c->changeStatus(
            $request,
            (string) $request->getAttribute('id'),
        ));
    });
};
