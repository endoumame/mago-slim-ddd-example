<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Task;

use App\Application\Task\Command\CreateTaskCommand;
use App\Application\Task\Handler\CreateTaskHandler;
use App\Application\Task\Handler\GetTaskHandler;
use App\Application\Task\Query\GetTaskQuery;
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

        // @mago-expect analysis:impossible-type-comparison
        self::assertTrue($result->isOk());
        self::assertSame('Find me', $result->unwrap()->title->value());
    }

    /**
     * @throws \Throwable
     */
    public function testGetNonExistentTask(): void
    {
        $result = $this->handler->handle(new GetTaskQuery(id: Uuid::uuid4()->toString()));

        // @mago-expect analysis:impossible-type-comparison
        self::assertTrue($result->isErr());
        self::assertInstanceOf(TaskNotFoundException::class, $result->unwrapErr());
    }

    /**
     * @throws \Throwable
     */
    public function testGetWithInvalidId(): void
    {
        $result = $this->handler->handle(new GetTaskQuery(id: 'not-a-uuid'));

        // @mago-expect analysis:impossible-type-comparison
        self::assertTrue($result->isErr());
        self::assertInstanceOf(InvalidTaskIdException::class, $result->unwrapErr());
    }
}
