<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Task;

use App\Application\Task\Command\CreateTaskCommand;
use App\Application\Task\Handler\CreateTaskHandler;
use App\Domain\Task\Exception\InvalidDueDateException;
use App\Domain\Task\Exception\InvalidTaskTitleException;
use App\Domain\Task\TaskStatus;
use App\Domain\Task\TodoTask;
use App\Infrastructure\Persistence\InMemoryTaskRepository;
use PHPUnit\Framework\TestCase;

final class CreateTaskHandlerTest extends TestCase
{
    private CreateTaskHandler $handler;
    private InMemoryTaskRepository $repository;

    /**
     * @throws \Throwable
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->repository = new InMemoryTaskRepository();
        $this->handler = new CreateTaskHandler($this->repository);
    }

    /**
     * @throws \Throwable
     */
    public function testCreateTaskSucceeds(): void
    {
        $command = new CreateTaskCommand(title: 'Buy groceries');

        $result = $this->handler->handle($command);

        $task = $result->unwrap();
        self::assertInstanceOf(TodoTask::class, $task);
        self::assertSame('Buy groceries', $task->title->value());
        self::assertSame('', $task->description->value());
        self::assertSame(TaskStatus::Todo, $task->status);
        self::assertNull($task->dueDate);
    }

    /**
     * @throws \Throwable
     */
    public function testCreateTaskWithDescriptionAndDueDate(): void
    {
        $futureDate = new \DateTimeImmutable('+7 days')->format('Y-m-d');
        $command = new CreateTaskCommand(
            title: 'Important task',
            description: 'This is important',
            dueDate: $futureDate,
        );

        $result = $this->handler->handle($command);

        $task = $result->unwrap();
        self::assertSame('Important task', $task->title->value());
        self::assertSame('This is important', $task->description->value());
        self::assertNotNull($task->dueDate);
        self::assertSame($futureDate, $task->dueDate->format());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateTaskIsSavedToRepository(): void
    {
        $command = new CreateTaskCommand(title: 'Saved task');
        $result = $this->handler->handle($command);
        $task = $result->unwrap();

        $found = $this->repository->findById($task->id);
        self::assertSame($task->id->value(), $found->unwrap()->id->value());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateTaskWithEmptyTitleFails(): void
    {
        $command = new CreateTaskCommand(title: '');

        $result = $this->handler->handle($command);

        self::assertInstanceOf(InvalidTaskTitleException::class, $result->unwrapErr());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateTaskWithInvalidDueDateFails(): void
    {
        $command = new CreateTaskCommand(title: 'Valid title', dueDate: 'not-a-date');

        $result = $this->handler->handle($command);

        self::assertInstanceOf(InvalidDueDateException::class, $result->unwrapErr());
    }
}
