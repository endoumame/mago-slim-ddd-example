<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Task\ChangeStatus;

use App\Application\Task\ChangeStatus\TaskChangeStatusCommand;
use App\Application\Task\ChangeStatus\TaskChangeStatusCommandHandler;
use App\Application\Task\Create\TaskCreateCommand;
use App\Application\Task\Create\TaskCreateCommandHandler;
use App\Domain\Task\DoneTask;
use App\Domain\Task\Exception\InvalidTaskStatusTransitionException;
use App\Domain\Task\InProgressTask;
use App\Domain\Task\TaskStatus;
use App\Infrastructure\Persistence\InMemoryTaskRepository;
use PHPUnit\Framework\TestCase;

final class TaskChangeStatusCommandHandlerTest extends TestCase
{
    private TaskChangeStatusCommandHandler $handler;
    private TaskCreateCommandHandler $createHandler;
    private InMemoryTaskRepository $repository;

    /**
     * @throws \Throwable
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->repository = new InMemoryTaskRepository();
        $this->handler = new TaskChangeStatusCommandHandler($this->repository);
        $this->createHandler = new TaskCreateCommandHandler($this->repository);
    }

    /**
     * @throws \Throwable
     */
    public function testChangeStatusTodoToInProgress(): void
    {
        $task = $this->createHandler->handle(new TaskCreateCommand(title: 'Task'))->unwrap();

        $result = $this->handler->handle(new TaskChangeStatusCommand(id: $task->id->value(), status: 'in_progress'));

        static::assertInstanceOf(InProgressTask::class, $result->unwrap());
        static::assertSame(TaskStatus::InProgress, $result->unwrap()->status);
    }

    /**
     * @throws \Throwable
     */
    public function testChangeStatusInProgressToDone(): void
    {
        $task = $this->createHandler->handle(new TaskCreateCommand(title: 'Task'))->unwrap();
        $this->handler->handle(new TaskChangeStatusCommand(id: $task->id->value(), status: 'in_progress'));

        $result = $this->handler->handle(new TaskChangeStatusCommand(id: $task->id->value(), status: 'done'));

        static::assertInstanceOf(DoneTask::class, $result->unwrap());
        static::assertSame(TaskStatus::Done, $result->unwrap()->status);
    }

    /**
     * @throws \Throwable
     */
    public function testInvalidTransitionFails(): void
    {
        $task = $this->createHandler->handle(new TaskCreateCommand(title: 'Task'))->unwrap();

        $result = $this->handler->handle(new TaskChangeStatusCommand(id: $task->id->value(), status: 'done'));

        static::assertInstanceOf(InvalidTaskStatusTransitionException::class, $result->unwrapErr());
    }

    /**
     * @throws \Throwable
     */
    public function testInvalidStatusValueFails(): void
    {
        $task = $this->createHandler->handle(new TaskCreateCommand(title: 'Task'))->unwrap();

        $result = $this->handler->handle(new TaskChangeStatusCommand(id: $task->id->value(), status: 'invalid'));

        static::assertInstanceOf(\InvalidArgumentException::class, $result->unwrapErr());
    }
}
