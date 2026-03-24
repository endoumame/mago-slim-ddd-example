<?php

declare(strict_types=1);

use App\Application\Task\Handler\ChangeTaskStatusHandler;
use App\Application\Task\Handler\CreateTaskHandler;
use App\Application\Task\Handler\DeleteTaskHandler;
use App\Application\Task\Handler\GetTaskHandler;
use App\Application\Task\Handler\ListTasksHandler;
use App\Application\Task\Handler\UpdateTaskHandler;
use App\Domain\Task\TaskRepositoryInterface;
use App\Infrastructure\Http\Action\ChangeTaskStatusAction;
use App\Infrastructure\Http\Action\CreateTaskAction;
use App\Infrastructure\Http\Action\DeleteTaskAction;
use App\Infrastructure\Http\Action\GetTaskAction;
use App\Infrastructure\Http\Action\ListTasksAction;
use App\Infrastructure\Http\Action\UpdateTaskAction;
use App\Infrastructure\Http\JsonResponseFactory;
use App\Infrastructure\Persistence\InMemoryTaskRepository;

return [
    TaskRepositoryInterface::class => static fn(): TaskRepositoryInterface => new InMemoryTaskRepository(),
    JsonResponseFactory::class => static fn(): JsonResponseFactory => new JsonResponseFactory(),
    CreateTaskHandler::class => static fn(TaskRepositoryInterface $r): CreateTaskHandler => new CreateTaskHandler($r),
    UpdateTaskHandler::class => static fn(TaskRepositoryInterface $r): UpdateTaskHandler => new UpdateTaskHandler($r),
    DeleteTaskHandler::class => static fn(TaskRepositoryInterface $r): DeleteTaskHandler => new DeleteTaskHandler($r),
    GetTaskHandler::class => static fn(TaskRepositoryInterface $r): GetTaskHandler => new GetTaskHandler($r),
    ListTasksHandler::class => static fn(TaskRepositoryInterface $r): ListTasksHandler => new ListTasksHandler($r),
    ChangeTaskStatusHandler::class =>
        static fn(TaskRepositoryInterface $r): ChangeTaskStatusHandler => new ChangeTaskStatusHandler($r),
    CreateTaskAction::class => static fn(
        CreateTaskHandler $h,
        JsonResponseFactory $f,
    ): CreateTaskAction => new CreateTaskAction($h, $f),
    GetTaskAction::class => static fn(GetTaskHandler $h, JsonResponseFactory $f): GetTaskAction => new GetTaskAction(
        $h,
        $f,
    ),
    ListTasksAction::class => static fn(
        ListTasksHandler $h,
        JsonResponseFactory $f,
    ): ListTasksAction => new ListTasksAction($h, $f),
    UpdateTaskAction::class => static fn(
        UpdateTaskHandler $h,
        JsonResponseFactory $f,
    ): UpdateTaskAction => new UpdateTaskAction($h, $f),
    DeleteTaskAction::class => static fn(
        DeleteTaskHandler $h,
        JsonResponseFactory $f,
    ): DeleteTaskAction => new DeleteTaskAction($h, $f),
    ChangeTaskStatusAction::class => static fn(
        ChangeTaskStatusHandler $h,
        JsonResponseFactory $f,
    ): ChangeTaskStatusAction => new ChangeTaskStatusAction($h, $f),
];
