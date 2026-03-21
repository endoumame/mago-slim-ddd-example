<?php

declare(strict_types=1);

use App\Application\Task\Handler\ChangeTaskStatusHandler;
use App\Application\Task\Handler\CreateTaskHandler;
use App\Application\Task\Handler\DeleteTaskHandler;
use App\Application\Task\Handler\GetTaskHandler;
use App\Application\Task\Handler\ListTasksHandler;
use App\Application\Task\Handler\UpdateTaskHandler;
use App\Domain\Task\TaskRepositoryInterface;
use App\Infrastructure\Persistence\InMemoryTaskRepository;

return [
    TaskRepositoryInterface::class => static fn(): TaskRepositoryInterface => new InMemoryTaskRepository(),
    CreateTaskHandler::class => static fn(TaskRepositoryInterface $r): CreateTaskHandler => new CreateTaskHandler($r),
    UpdateTaskHandler::class => static fn(TaskRepositoryInterface $r): UpdateTaskHandler => new UpdateTaskHandler($r),
    DeleteTaskHandler::class => static fn(TaskRepositoryInterface $r): DeleteTaskHandler => new DeleteTaskHandler($r),
    GetTaskHandler::class => static fn(TaskRepositoryInterface $r): GetTaskHandler => new GetTaskHandler($r),
    ListTasksHandler::class => static fn(TaskRepositoryInterface $r): ListTasksHandler => new ListTasksHandler($r),
    ChangeTaskStatusHandler::class =>
        static fn(TaskRepositoryInterface $r): ChangeTaskStatusHandler => new ChangeTaskStatusHandler($r),
];
