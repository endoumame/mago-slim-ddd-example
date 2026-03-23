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
        $task = $this->createHandler->handle(new CreateTaskCommand(title: 'To delete'))->unwrap();

        $result = $this->handler->handle(new DeleteTaskCommand(id: $task->id->value()));

        // @mago-expect analysis:impossible-type-comparison
        self::assertTrue($result->isOk());
    }

    /**
     * @throws \Throwable
     */
    public function testDeletedTaskIsNoLongerFound(): void
    {
        $task = $this->createHandler->handle(new CreateTaskCommand(title: 'To delete'))->unwrap();

        $this->handler->handle(new DeleteTaskCommand(id: $task->id->value()));

        $findResult = $this->repository->findById($task->id);
        // @mago-expect analysis:impossible-type-comparison
        self::assertTrue($findResult->isErr());
        self::assertInstanceOf(TaskNotFoundException::class, $findResult->unwrapErr());
    }

    /**
     * @throws \Throwable
     */
    public function testDeleteNonExistentTaskFails(): void
    {
        $result = $this->handler->handle(new DeleteTaskCommand(id: Uuid::uuid4()->toString()));

        // @mago-expect analysis:impossible-type-comparison
        self::assertTrue($result->isErr());
        self::assertInstanceOf(TaskNotFoundException::class, $result->unwrapErr());
    }
}
