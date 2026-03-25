<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Task\Delete;

use App\Application\Task\Create\CreateTask;
use App\Application\Task\Create\CreateTaskHandler;
use App\Application\Task\Delete\DeleteTask;
use App\Application\Task\Delete\DeleteTaskHandler;
use App\Domain\Task\Exception\TaskNotFoundException;
use App\Infrastructure\Persistence\InMemoryTaskRepository;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class DeleteTaskHandlerTest extends TestCase
{
    private DeleteTaskHandler $handler;
    private CreateTaskHandler $createHandler;
    private InMemoryTaskRepository $repository;

    /**
     * @throws \Throwable
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->repository = new InMemoryTaskRepository();
        $this->handler = new DeleteTaskHandler($this->repository);
        $this->createHandler = new CreateTaskHandler($this->repository);
    }

    /**
     * @throws \Throwable
     */
    public function testDeleteExistingTaskSucceeds(): void
    {
        $task = $this->createHandler->handle(new CreateTask(title: 'To delete'))->unwrap();

        $result = $this->handler->handle(new DeleteTask(id: $task->id->value()));

        static::assertNotNull($result->unwrap());
    }

    /**
     * @throws \Throwable
     */
    public function testDeletedTaskIsNoLongerFound(): void
    {
        $task = $this->createHandler->handle(new CreateTask(title: 'To delete'))->unwrap();

        $this->handler->handle(new DeleteTask(id: $task->id->value()));

        $findResult = $this->repository->findById($task->id);
        static::assertInstanceOf(TaskNotFoundException::class, $findResult->unwrapErr());
    }

    /**
     * @throws \Throwable
     */
    public function testDeleteNonExistentTaskFails(): void
    {
        $result = $this->handler->handle(new DeleteTask(id: Uuid::uuid4()->toString()));

        static::assertInstanceOf(TaskNotFoundException::class, $result->unwrapErr());
    }
}
