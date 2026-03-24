<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Task;

use App\Application\Task\Command\ChangeTaskStatusCommand;
use App\Application\Task\Command\CreateTaskCommand;
use App\Application\Task\Handler\ChangeTaskStatusHandler;
use App\Application\Task\Handler\CreateTaskHandler;
use App\Domain\Task\DoneTask;
use App\Domain\Task\Exception\InvalidTaskStatusTransitionException;
use App\Domain\Task\InProgressTask;
use App\Domain\Task\TaskStatus;
use App\Infrastructure\Persistence\InMemoryTaskRepository;
use PHPUnit\Framework\TestCase;

final class ChangeTaskStatusHandlerTest extends TestCase
{
    private ChangeTaskStatusHandler $handler;
    private CreateTaskHandler $createHandler;
    private InMemoryTaskRepository $repository;

    /**
     * @throws \Throwable
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->repository = new InMemoryTaskRepository();
        $this->handler = new ChangeTaskStatusHandler($this->repository);
        $this->createHandler = new CreateTaskHandler($this->repository);
    }

    /**
     * @throws \Throwable
     */
    public function testChangeStatusTodoToInProgress(): void
    {
        $task = $this->createHandler->handle(new CreateTaskCommand(title: 'Task'))->unwrap();

        $result = $this->handler->handle(new ChangeTaskStatusCommand(id: $task->id->value(), status: 'in_progress'));

        static::assertInstanceOf(InProgressTask::class, $result->unwrap());
        static::assertSame(TaskStatus::InProgress, $result->unwrap()->status);
    }

    /**
     * @throws \Throwable
     */
    public function testChangeStatusInProgressToDone(): void
    {
        $task = $this->createHandler->handle(new CreateTaskCommand(title: 'Task'))->unwrap();
        $this->handler->handle(new ChangeTaskStatusCommand(id: $task->id->value(), status: 'in_progress'));

        $result = $this->handler->handle(new ChangeTaskStatusCommand(id: $task->id->value(), status: 'done'));

        static::assertInstanceOf(DoneTask::class, $result->unwrap());
        static::assertSame(TaskStatus::Done, $result->unwrap()->status);
    }

    /**
     * @throws \Throwable
     */
    public function testInvalidTransitionFails(): void
    {
        $task = $this->createHandler->handle(new CreateTaskCommand(title: 'Task'))->unwrap();

        $result = $this->handler->handle(new ChangeTaskStatusCommand(id: $task->id->value(), status: 'done'));

        static::assertInstanceOf(InvalidTaskStatusTransitionException::class, $result->unwrapErr());
    }

    /**
     * @throws \Throwable
     */
    public function testInvalidStatusValueFails(): void
    {
        $task = $this->createHandler->handle(new CreateTaskCommand(title: 'Task'))->unwrap();

        $result = $this->handler->handle(new ChangeTaskStatusCommand(id: $task->id->value(), status: 'invalid'));

        static::assertInstanceOf(\InvalidArgumentException::class, $result->unwrapErr());
    }
}
