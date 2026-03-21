<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Task;

use App\Application\Task\Command\CreateTaskCommand;
use App\Application\Task\Handler\CreateTaskHandler;
use App\Domain\Task\Exception\InvalidDueDateException;
use App\Domain\Task\Exception\InvalidTaskTitleException;
use App\Domain\Task\Task;
use App\Domain\Task\TaskStatus;
use App\Infrastructure\Persistence\InMemoryTaskRepository;
use PHPUnit\Framework\TestCase;

final class CreateTaskHandlerTest extends TestCase
{
    private CreateTaskHandler $handler;
    private InMemoryTaskRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new InMemoryTaskRepository();
        $this->handler = new CreateTaskHandler($this->repository);
    }

    public function testCreateTaskSucceeds(): void
    {
        $command = new CreateTaskCommand(title: 'Buy groceries');

        $result = $this->handler->handle($command);

        self::assertTrue($result->isSucceeded());

        $task = $result->getResult();
        self::assertInstanceOf(Task::class, $task);
        self::assertSame('Buy groceries', $task->title->value());
        self::assertSame('', $task->description->value());
        self::assertSame(TaskStatus::Todo, $task->status);
        self::assertNull($task->dueDate);
    }

    public function testCreateTaskWithDescriptionAndDueDate(): void
    {
        $futureDate = new \DateTimeImmutable('+7 days')->format('Y-m-d');
        $command = new CreateTaskCommand(
            title: 'Important task',
            description: 'This is important',
            dueDate: $futureDate,
        );

        $result = $this->handler->handle($command);

        self::assertTrue($result->isSucceeded());

        $task = $result->getResult();
        self::assertSame('Important task', $task->title->value());
        self::assertSame('This is important', $task->description->value());
        self::assertNotNull($task->dueDate);
        self::assertSame($futureDate, $task->dueDate->format());
    }

    public function testCreateTaskIsSavedToRepository(): void
    {
        $command = new CreateTaskCommand(title: 'Saved task');
        $result = $this->handler->handle($command);
        $task = $result->getResult();

        $found = $this->repository->findById($task->id);
        self::assertTrue($found->isSucceeded());
        self::assertSame($task->id->value(), $found->getResult()->id->value());
    }

    public function testCreateTaskWithEmptyTitleFails(): void
    {
        $command = new CreateTaskCommand(title: '');

        $result = $this->handler->handle($command);

        self::assertTrue($result->isFailed());
        self::assertInstanceOf(InvalidTaskTitleException::class, $result->getThrowable());
    }

    public function testCreateTaskWithInvalidDueDateFails(): void
    {
        $command = new CreateTaskCommand(title: 'Valid title', dueDate: 'not-a-date');

        $result = $this->handler->handle($command);

        self::assertTrue($result->isFailed());
        self::assertInstanceOf(InvalidDueDateException::class, $result->getThrowable());
    }
}
