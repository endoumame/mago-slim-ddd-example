<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Task;

use App\Application\Task\Command\CreateTaskCommand;
use App\Application\Task\Command\DeleteTaskCommand;
use App\Application\Task\Handler\CreateTaskHandler;
use App\Application\Task\Handler\DeleteTaskHandler;
use App\Domain\Task\Exception\TaskNotFoundException;
use App\Infrastructure\Persistence\InMemoryTaskRepository;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class DeleteTaskHandlerTest extends TestCase
{
    private DeleteTaskHandler $handler;
    private CreateTaskHandler $createHandler;
    private InMemoryTaskRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new InMemoryTaskRepository();
        $this->handler = new DeleteTaskHandler($this->repository);
        $this->createHandler = new CreateTaskHandler($this->repository);
    }

    public function testDeleteExistingTaskSucceeds(): void
    {
        $task = $this->createHandler->handle(new CreateTaskCommand(title: 'To delete'))->getResult();

        $result = $this->handler->handle(new DeleteTaskCommand(id: $task->id->value()));

        self::assertTrue($result->isSucceeded());
    }

    public function testDeletedTaskIsNoLongerFound(): void
    {
        $task = $this->createHandler->handle(new CreateTaskCommand(title: 'To delete'))->getResult();

        $this->handler->handle(new DeleteTaskCommand(id: $task->id->value()));

        $findResult = $this->repository->findById($task->id);
        self::assertTrue($findResult->isFailed());
        self::assertInstanceOf(TaskNotFoundException::class, $findResult->getThrowable());
    }

    public function testDeleteNonExistentTaskFails(): void
    {
        $result = $this->handler->handle(new DeleteTaskCommand(id: Uuid::uuid4()->toString()));

        self::assertTrue($result->isFailed());
        self::assertInstanceOf(TaskNotFoundException::class, $result->getThrowable());
    }
}
