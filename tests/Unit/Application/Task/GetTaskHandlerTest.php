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
        $task = $this->createHandler->handle(new CreateTaskCommand(title: 'Find me'))->getResult();

        $result = $this->handler->handle(new GetTaskQuery(id: $task->id->value()));

        self::assertTrue($result->isSucceeded());
        self::assertSame('Find me', $result->getResult()->title->value());
    }

    /**
     * @throws \Throwable
     */
    public function testGetNonExistentTask(): void
    {
        $result = $this->handler->handle(new GetTaskQuery(id: Uuid::uuid4()->toString()));

        self::assertTrue($result->isFailed());
        self::assertInstanceOf(TaskNotFoundException::class, $result->getThrowable());
    }

    /**
     * @throws \Throwable
     */
    public function testGetWithInvalidId(): void
    {
        $result = $this->handler->handle(new GetTaskQuery(id: 'not-a-uuid'));

        self::assertTrue($result->isFailed());
        self::assertInstanceOf(InvalidTaskIdException::class, $result->getThrowable());
    }
}
