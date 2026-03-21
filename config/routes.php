<?php

declare(strict_types=1);

use App\Infrastructure\Http\Controller\TaskController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app): void {
    $app->group('/api', function (RouteCollectorProxy $group): void {
        $group->get('/tasks', [TaskController::class, 'list']);
        $group->post('/tasks', [TaskController::class, 'create']);
        $group->get('/tasks/{id}', [TaskController::class, 'get']);
        $group->put('/tasks/{id}', [TaskController::class, 'update']);
        $group->delete('/tasks/{id}', [TaskController::class, 'delete']);
        $group->patch('/tasks/{id}/status', [TaskController::class, 'changeStatus']);
    });
};
