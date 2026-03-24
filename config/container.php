<?php

declare(strict_types=1);

use App\Application\Task\ChangeStatus\TaskChangeStatusCommandHandler;
use App\Application\Task\Create\TaskCreateCommandHandler;
use App\Application\Task\Delete\TaskDeleteCommandHandler;
use App\Application\Task\Get\TaskGetQueryHandler;
use App\Application\Task\List\TaskListQueryHandler;
use App\Application\Task\Update\TaskUpdateCommandHandler;
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
    TaskCreateCommandHandler::class =>
        static fn(TaskRepositoryInterface $r): TaskCreateCommandHandler => new TaskCreateCommandHandler($r),
    TaskUpdateCommandHandler::class =>
        static fn(TaskRepositoryInterface $r): TaskUpdateCommandHandler => new TaskUpdateCommandHandler($r),
    TaskDeleteCommandHandler::class =>
        static fn(TaskRepositoryInterface $r): TaskDeleteCommandHandler => new TaskDeleteCommandHandler($r),
    TaskGetQueryHandler::class => static fn(TaskRepositoryInterface $r): TaskGetQueryHandler => new TaskGetQueryHandler(
        $r,
    ),
    TaskListQueryHandler::class =>
        static fn(TaskRepositoryInterface $r): TaskListQueryHandler => new TaskListQueryHandler($r),
    TaskChangeStatusCommandHandler::class =>
        static fn(TaskRepositoryInterface $r): TaskChangeStatusCommandHandler => new TaskChangeStatusCommandHandler($r),
    CreateTaskAction::class => static fn(TaskCreateCommandHandler $h): CreateTaskAction => new CreateTaskAction($h),
    GetTaskAction::class => static fn(TaskGetQueryHandler $h): GetTaskAction => new GetTaskAction($h),
    ListTasksAction::class => static fn(TaskListQueryHandler $h): ListTasksAction => new ListTasksAction($h),
    UpdateTaskAction::class => static fn(TaskUpdateCommandHandler $h): UpdateTaskAction => new UpdateTaskAction($h),
    DeleteTaskAction::class => static fn(TaskDeleteCommandHandler $h): DeleteTaskAction => new DeleteTaskAction($h),
    ChangeTaskStatusAction::class =>
        static fn(TaskChangeStatusCommandHandler $h): ChangeTaskStatusAction => new ChangeTaskStatusAction($h),
];
