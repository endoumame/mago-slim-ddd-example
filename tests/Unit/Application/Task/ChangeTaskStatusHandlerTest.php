<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Task;

use App\Application\Task\Command\ChangeTaskStatusCommand;
use App\Application\Task\Command\CreateTaskCommand;
use App\Application\Task\Handler\ChangeTaskStatusHandler;
use App\Application\Task\Handler\CreateTaskHandler;
use App\Domain\Task\Exception\InvalidTaskStatusTransitionException;
use App\Domain\Task\TaskStatus;
use App\Infrastructure\Persistence\InMemoryTaskRepository;
use PHPUnit\Framework\TestCase;

final class ChangeTaskStatusHandlerTest extends TestCase
{
    private ChangeTaskStatusHandler $handler;
    private CreateTaskHandler $createHandler;
    private InMemoryTaskRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new InMemoryTaskRepository();
        $this->handler = new ChangeTaskStatusHandler($this->repository);
        $this->createHandler = new CreateTaskHandler($this->repository);
    }

    public function testChangeStatusTodoToInProgress(): void
    {
        $task = $this->createHandler->handle(new CreateTaskCommand(title: 'Task'))->getResult();

        $result = $this->handler->handle(new ChangeTaskStatusCommand(id: $task->id->value(), status: 'in_progress'));

        self::assertTrue($result->isSucceeded());
        self::assertSame(TaskStatus::InProgress, $result->getResult()->status);
    }

    public function testChangeStatusInProgressToDone(): void
    {
        $task = $this->createHandler->handle(new CreateTaskCommand(title: 'Task'))->getResult();
        $this->handler->handle(new ChangeTaskStatusCommand(id: $task->id->value(), status: 'in_progress'));

        $result = $this->handler->handle(new ChangeTaskStatusCommand(id: $task->id->value(), status: 'done'));

        self::assertTrue($result->isSucceeded());
        self::assertSame(TaskStatus::Done, $result->getResult()->status);
    }

    public function testInvalidTransitionFails(): void
    {
        $task = $this->createHandler->handle(new CreateTaskCommand(title: 'Task'))->getResult();

        $result = $this->handler->handle(new ChangeTaskStatusCommand(id: $task->id->value(), status: 'done'));

        self::assertTrue($result->isFailed());
        self::assertInstanceOf(InvalidTaskStatusTransitionException::class, $result->getThrowable());
    }

    public function testInvalidStatusValueFails(): void
    {
        $task = $this->createHandler->handle(new CreateTaskCommand(title: 'Task'))->getResult();

        $result = $this->handler->handle(new ChangeTaskStatusCommand(id: $task->id->value(), status: 'invalid'));

        self::assertTrue($result->isFailed());
        self::assertInstanceOf(\InvalidArgumentException::class, $result->getThrowable());
    }
}
