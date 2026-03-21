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

    protected function setUp(): void
    {
        $this->repository = new InMemoryTaskRepository();
        $this->handler = new UpdateTaskHandler($this->repository);
        $this->createHandler = new CreateTaskHandler($this->repository);
    }

    public function testUpdateTitleSucceeds(): void
    {
        $task = $this->createHandler->handle(new CreateTaskCommand(title: 'Original'))->getResult();

        $result = $this->handler->handle(new UpdateTaskCommand(id: $task->id->value(), title: 'Updated'));

        self::assertTrue($result->isSucceeded());
        self::assertSame('Updated', $result->getResult()->title->value());
    }

    public function testUpdateDescriptionSucceeds(): void
    {
        $task = $this->createHandler->handle(new CreateTaskCommand(title: 'Task'))->getResult();

        $result = $this->handler->handle(new UpdateTaskCommand(id: $task->id->value(), description: 'New description'));

        self::assertTrue($result->isSucceeded());
        self::assertSame('New description', $result->getResult()->description->value());
    }

    public function testUpdateNonExistentTaskFails(): void
    {
        $result = $this->handler->handle(new UpdateTaskCommand(id: Uuid::uuid4()->toString(), title: 'Will fail'));

        self::assertTrue($result->isFailed());
        self::assertInstanceOf(TaskNotFoundException::class, $result->getThrowable());
    }

    public function testUpdateWithInvalidTitleFails(): void
    {
        $task = $this->createHandler->handle(new CreateTaskCommand(title: 'Task'))->getResult();

        $result = $this->handler->handle(new UpdateTaskCommand(id: $task->id->value(), title: ''));

        self::assertTrue($result->isFailed());
        self::assertInstanceOf(InvalidTaskTitleException::class, $result->getThrowable());
    }
}
