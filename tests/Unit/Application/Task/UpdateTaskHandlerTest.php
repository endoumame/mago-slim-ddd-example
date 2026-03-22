<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Task;

use App\Application\Task\Command\CreateTaskCommand;
use App\Application\Task\Command\UpdateTaskCommand;
use App\Application\Task\Handler\CreateTaskHandler;
use App\Application\Task\Handler\UpdateTaskHandler;
use App\Domain\Task\Exception\InvalidTaskTitleException;
use App\Domain\Task\Exception\TaskNotFoundException;
use App\Infrastructure\Persistence\InMemoryTaskRepository;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class UpdateTaskHandlerTest extends TestCase
{
    private UpdateTaskHandler $handler;
    private CreateTaskHandler $createHandler;
    private InMemoryTaskRepository $repository;

    /**
     * @throws \Throwable
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->repository = new InMemoryTaskRepository();
        $this->handler = new UpdateTaskHandler($this->repository);
        $this->createHandler = new CreateTaskHandler($this->repository);
    }

    /**
     * @throws \Throwable
     */
    public function testUpdateTitleSucceeds(): void
    {
        $task = $this->createHandler->handle(new CreateTaskCommand(title: 'Original'))->unwrap();

        $result = $this->handler->handle(new UpdateTaskCommand(id: $task->id->value(), title: 'Updated'));

        self::assertTrue($result->isOk());
        self::assertSame('Updated', $result->unwrap()->title->value());
    }

    /**
     * @throws \Throwable
     */
    public function testUpdateDescriptionSucceeds(): void
    {
        $task = $this->createHandler->handle(new CreateTaskCommand(title: 'Task'))->unwrap();

        $result = $this->handler->handle(new UpdateTaskCommand(id: $task->id->value(), description: 'New description'));

        self::assertTrue($result->isOk());
        self::assertSame('New description', $result->unwrap()->description->value());
    }

    /**
     * @throws \Throwable
     */
    public function testUpdateNonExistentTaskFails(): void
    {
        $result = $this->handler->handle(new UpdateTaskCommand(id: Uuid::uuid4()->toString(), title: 'Will fail'));

        self::assertTrue($result->isErr());
        self::assertInstanceOf(TaskNotFoundException::class, $result->unwrapErr());
    }

    /**
     * @throws \Throwable
     */
    public function testUpdateWithInvalidTitleFails(): void
    {
        $task = $this->createHandler->handle(new CreateTaskCommand(title: 'Task'))->unwrap();

        $result = $this->handler->handle(new UpdateTaskCommand(id: $task->id->value(), title: ''));

        self::assertTrue($result->isErr());
        self::assertInstanceOf(InvalidTaskTitleException::class, $result->unwrapErr());
    }
}
