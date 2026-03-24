<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Task\Delete;

use App\Application\Task\Create\TaskCreateCommand;
use App\Application\Task\Create\TaskCreateCommandHandler;
use App\Application\Task\Delete\TaskDeleteCommand;
use App\Application\Task\Delete\TaskDeleteCommandHandler;
use App\Domain\Task\Exception\TaskNotFoundException;
use App\Infrastructure\Persistence\InMemoryTaskRepository;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class TaskDeleteCommandHandlerTest extends TestCase
{
    private TaskDeleteCommandHandler $handler;
    private TaskCreateCommandHandler $createHandler;
    private InMemoryTaskRepository $repository;

    /**
     * @throws \Throwable
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->repository = new InMemoryTaskRepository();
        $this->handler = new TaskDeleteCommandHandler($this->repository);
        $this->createHandler = new TaskCreateCommandHandler($this->repository);
    }

    /**
     * @throws \Throwable
     */
    public function testDeleteExistingTaskSucceeds(): void
    {
        $task = $this->createHandler->handle(new TaskCreateCommand(title: 'To delete'))->unwrap();

        $result = $this->handler->handle(new TaskDeleteCommand(id: $task->id->value()));

        static::assertNotNull($result->unwrap());
    }

    /**
     * @throws \Throwable
     */
    public function testDeletedTaskIsNoLongerFound(): void
    {
        $task = $this->createHandler->handle(new TaskCreateCommand(title: 'To delete'))->unwrap();

        $this->handler->handle(new TaskDeleteCommand(id: $task->id->value()));

        $findResult = $this->repository->findById($task->id);
        static::assertInstanceOf(TaskNotFoundException::class, $findResult->unwrapErr());
    }

    /**
     * @throws \Throwable
     */
    public function testDeleteNonExistentTaskFails(): void
    {
        $result = $this->handler->handle(new TaskDeleteCommand(id: Uuid::uuid4()->toString()));

        static::assertInstanceOf(TaskNotFoundException::class, $result->unwrapErr());
    }
}
