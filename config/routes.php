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
        // Arrow functions are required here — first-class callables ($c->list(...)) break
        // because Slim's CallableResolver::bindToContainer() rebinds $this to the container.
        // @mago-expect lint:prefer-first-class-callable
        $group->get('/tasks', fn(ServerRequestInterface $request): ResponseInterface => $c->list($request));
        // @mago-expect lint:prefer-first-class-callable
        $group->post('/tasks', fn(ServerRequestInterface $request): ResponseInterface => $c->create($request));
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
