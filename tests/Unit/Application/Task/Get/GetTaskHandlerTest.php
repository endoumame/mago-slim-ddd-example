<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Task\Get;

use App\Application\Task\Create\CreateTaskCommand;
use App\Application\Task\Create\CreateTaskHandler;
use App\Application\Task\Get\GetTaskHandler;
use App\Application\Task\Get\GetTaskQuery;
use App\Domain\Task\Exception\InvalidTaskIdException;
use App\Domain\Task\Exception\TaskNotFoundException;
use App\Infrastructure\Persistence\InMemoryTaskRepository;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class GetTaskHandlerTest extends TestCase
{
    private GetTaskHandler $handler;
    private CreateTaskHandler $createHandler;

    /**
     * @throws \Throwable
     */
    #[\Override]
    protected function setUp(): void
    {
        $repository = new InMemoryTaskRepository();
        $this->handler = new GetTaskHandler($repository);
        $this->createHandler = new CreateTaskHandler($repository);
    }

    /**
     * @throws \Throwable
     */
    public function testGetExistingTask(): void
    {
        $task = $this->createHandler->handle(new CreateTaskCommand(title: 'Find me'))->unwrap();

        $result = $this->handler->handle(new GetTaskQuery(id: $task->id->value()));

        static::assertSame('Find me', $result->unwrap()->title->value());
    }

    /**
     * @throws \Throwable
     */
    public function testGetNonExistentTask(): void
    {
        $result = $this->handler->handle(new GetTaskQuery(id: Uuid::uuid4()->toString()));

        static::assertInstanceOf(TaskNotFoundException::class, $result->unwrapErr());
    }

    /**
     * @throws \Throwable
     */
    public function testGetWithInvalidId(): void
    {
        $result = $this->handler->handle(new GetTaskQuery(id: 'not-a-uuid'));

        static::assertInstanceOf(InvalidTaskIdException::class, $result->unwrapErr());
    }
}
