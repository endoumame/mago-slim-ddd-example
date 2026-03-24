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
        static::assertInstanceOf(TodoTask::class, $task);
        static::assertSame('Buy groceries', $task->title->value());
        static::assertSame('', $task->description->value());
        static::assertSame(TaskStatus::Todo, $task->status);
        static::assertNull($task->dueDate);
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
        static::assertSame('Important task', $task->title->value());
        static::assertSame('This is important', $task->description->value());
        static::assertNotNull($task->dueDate);
        static::assertSame($futureDate, $task->dueDate->format());
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
        static::assertSame($task->id->value(), $found->unwrap()->id->value());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateTaskWithEmptyTitleFails(): void
    {
        $command = new CreateTaskCommand(title: '');

        $result = $this->handler->handle($command);

        static::assertInstanceOf(InvalidTaskTitleException::class, $result->unwrapErr());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateTaskWithInvalidDueDateFails(): void
    {
        $command = new CreateTaskCommand(title: 'Valid title', dueDate: 'not-a-date');

        $result = $this->handler->handle($command);

        static::assertInstanceOf(InvalidDueDateException::class, $result->unwrapErr());
    }
}
