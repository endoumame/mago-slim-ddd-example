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

        // @mago-expect analysis:impossible-type-comparison
        self::assertTrue($result->isOk());
        self::assertInstanceOf(InProgressTask::class, $result->unwrap());
        self::assertSame(TaskStatus::InProgress, $result->unwrap()->status);
    }

    /**
     * @throws \Throwable
     */
    public function testChangeStatusInProgressToDone(): void
    {
        $task = $this->createHandler->handle(new CreateTaskCommand(title: 'Task'))->unwrap();
        $this->handler->handle(new ChangeTaskStatusCommand(id: $task->id->value(), status: 'in_progress'));

        $result = $this->handler->handle(new ChangeTaskStatusCommand(id: $task->id->value(), status: 'done'));

        // @mago-expect analysis:impossible-type-comparison
        self::assertTrue($result->isOk());
        self::assertInstanceOf(DoneTask::class, $result->unwrap());
        self::assertSame(TaskStatus::Done, $result->unwrap()->status);
    }

    /**
     * @throws \Throwable
     */
    public function testInvalidTransitionFails(): void
    {
        $task = $this->createHandler->handle(new CreateTaskCommand(title: 'Task'))->unwrap();

        $result = $this->handler->handle(new ChangeTaskStatusCommand(id: $task->id->value(), status: 'done'));

        // @mago-expect analysis:impossible-type-comparison
        self::assertTrue($result->isErr());
        self::assertInstanceOf(InvalidTaskStatusTransitionException::class, $result->unwrapErr());
    }

    /**
     * @throws \Throwable
     */
    public function testInvalidStatusValueFails(): void
    {
        $task = $this->createHandler->handle(new CreateTaskCommand(title: 'Task'))->unwrap();

        $result = $this->handler->handle(new ChangeTaskStatusCommand(id: $task->id->value(), status: 'invalid'));

        // @mago-expect analysis:impossible-type-comparison
        self::assertTrue($result->isErr());
        self::assertInstanceOf(\InvalidArgumentException::class, $result->unwrapErr());
    }
}
