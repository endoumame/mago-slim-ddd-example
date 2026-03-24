<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Task\Update;

use App\Application\Task\Create\CreateTaskCommand;
use App\Application\Task\Create\CreateTaskHandler;
use App\Application\Task\Update\UpdateTaskCommand;
use App\Application\Task\Update\UpdateTaskHandler;
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

        static::assertSame('Updated', $result->unwrap()->title->value());
    }

    /**
     * @throws \Throwable
     */
    public function testUpdateDescriptionSucceeds(): void
    {
        $task = $this->createHandler->handle(new CreateTaskCommand(title: 'Task'))->unwrap();

        $result = $this->handler->handle(new UpdateTaskCommand(id: $task->id->value(), description: 'New description'));

        static::assertSame('New description', $result->unwrap()->description->value());
    }

    /**
     * @throws \Throwable
     */
    public function testUpdateNonExistentTaskFails(): void
    {
        $result = $this->handler->handle(new UpdateTaskCommand(id: Uuid::uuid4()->toString(), title: 'Will fail'));

        static::assertInstanceOf(TaskNotFoundException::class, $result->unwrapErr());
    }

    /**
     * @throws \Throwable
     */
    public function testUpdateWithInvalidTitleFails(): void
    {
        $task = $this->createHandler->handle(new CreateTaskCommand(title: 'Task'))->unwrap();

        $result = $this->handler->handle(new UpdateTaskCommand(id: $task->id->value(), title: ''));

        static::assertInstanceOf(InvalidTaskTitleException::class, $result->unwrapErr());
    }
}
