<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Task\Get;

use App\Application\Task\Create\TaskCreateCommand;
use App\Application\Task\Create\TaskCreateCommandHandler;
use App\Application\Task\Get\TaskGetQuery;
use App\Application\Task\Get\TaskGetQueryHandler;
use App\Domain\Task\Exception\InvalidTaskIdException;
use App\Domain\Task\Exception\TaskNotFoundException;
use App\Infrastructure\Persistence\InMemoryTaskRepository;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class TaskGetQueryHandlerTest extends TestCase
{
    private TaskGetQueryHandler $handler;
    private TaskCreateCommandHandler $createHandler;

    /**
     * @throws \Throwable
     */
    #[\Override]
    protected function setUp(): void
    {
        $repository = new InMemoryTaskRepository();
        $this->handler = new TaskGetQueryHandler($repository);
        $this->createHandler = new TaskCreateCommandHandler($repository);
    }

    /**
     * @throws \Throwable
     */
    public function testGetExistingTask(): void
    {
        $task = $this->createHandler->handle(new TaskCreateCommand(title: 'Find me'))->unwrap();

        $result = $this->handler->handle(new TaskGetQuery(id: $task->id->value()));

        static::assertSame('Find me', $result->unwrap()->title->value());
    }

    /**
     * @throws \Throwable
     */
    public function testGetNonExistentTask(): void
    {
        $result = $this->handler->handle(new TaskGetQuery(id: Uuid::uuid4()->toString()));

        static::assertInstanceOf(TaskNotFoundException::class, $result->unwrapErr());
    }

    /**
     * @throws \Throwable
     */
    public function testGetWithInvalidId(): void
    {
        $result = $this->handler->handle(new TaskGetQuery(id: 'not-a-uuid'));

        static::assertInstanceOf(InvalidTaskIdException::class, $result->unwrapErr());
    }
}
