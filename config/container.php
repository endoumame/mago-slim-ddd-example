<?php

declare(strict_types=1);

use App\Application\Task\ChangeStatus\ChangeTaskStatusHandler;
use App\Application\Task\Create\CreateTaskHandler;
use App\Application\Task\Delete\DeleteTaskHandler;
use App\Application\Task\Get\GetTaskHandler;
use App\Application\Task\List\ListTasksHandler;
use App\Application\Task\Update\UpdateTaskHandler;
use App\Domain\Task\TaskRepositoryInterface;
use App\Infrastructure\Http\Action\ChangeTaskStatusAction;
use App\Infrastructure\Http\Action\CreateTaskAction;
use App\Infrastructure\Http\Action\DeleteTaskAction;
use App\Infrastructure\Http\Action\GetTaskAction;
use App\Infrastructure\Http\Action\ListTasksAction;
use App\Infrastructure\Http\Action\UpdateTaskAction;
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
    CreateTaskAction::class => static fn(CreateTaskHandler $h): CreateTaskAction => new CreateTaskAction($h),
    GetTaskAction::class => static fn(GetTaskHandler $h): GetTaskAction => new GetTaskAction($h),
    ListTasksAction::class => static fn(ListTasksHandler $h): ListTasksAction => new ListTasksAction($h),
    UpdateTaskAction::class => static fn(UpdateTaskHandler $h): UpdateTaskAction => new UpdateTaskAction($h),
    DeleteTaskAction::class => static fn(DeleteTaskHandler $h): DeleteTaskAction => new DeleteTaskAction($h),
    ChangeTaskStatusAction::class =>
        static fn(ChangeTaskStatusHandler $h): ChangeTaskStatusAction => new ChangeTaskStatusAction($h),
];
