<?php

declare(strict_types=1);

use App\Infrastructure\Http\Action\ChangeTaskStatusAction;
use App\Infrastructure\Http\Action\CreateTaskAction;
use App\Infrastructure\Http\Action\DeleteTaskAction;
use App\Infrastructure\Http\Action\GetTaskAction;
use App\Infrastructure\Http\Action\ListTasksAction;
use App\Infrastructure\Http\Action\UpdateTaskAction;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app): void {
    $container = $app->getContainer();

    /** @var ListTasksAction $listAction */
    $listAction = $container?->get(ListTasksAction::class);
    /** @var CreateTaskAction $createAction */
    $createAction = $container?->get(CreateTaskAction::class);
    /** @var GetTaskAction $getAction */
    $getAction = $container?->get(GetTaskAction::class);
    /** @var UpdateTaskAction $updateAction */
    $updateAction = $container?->get(UpdateTaskAction::class);
    /** @var DeleteTaskAction $deleteAction */
    $deleteAction = $container?->get(DeleteTaskAction::class);
    /** @var ChangeTaskStatusAction $changeStatusAction */
    $changeStatusAction = $container?->get(ChangeTaskStatusAction::class);

    $app->group('/api', function (RouteCollectorProxy $group) use (
        $listAction,
        $createAction,
        $getAction,
        $updateAction,
        $deleteAction,
        $changeStatusAction,
    ): void {
        $group->get('/tasks', $listAction(...));
        $group->post('/tasks', $createAction(...));
        $group->get('/tasks/{id}', fn(ServerRequestInterface $request): ResponseInterface => $getAction((string) $request->getAttribute(
            'id',
        )));
        $group->put('/tasks/{id}', fn(ServerRequestInterface $request): ResponseInterface => $updateAction(
            $request,
            (string) $request->getAttribute('id'),
        ));
        $group->delete('/tasks/{id}', fn(ServerRequestInterface $request): ResponseInterface => $deleteAction((string) $request->getAttribute(
            'id',
        )));
        $group->patch('/tasks/{id}/status', fn(ServerRequestInterface $request): ResponseInterface => $changeStatusAction(
            $request,
            (string) $request->getAttribute('id'),
        ));
    });
};
