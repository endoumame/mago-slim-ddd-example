<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Task\List;

use App\Application\Task\ChangeStatus\TaskChangeStatusCommand;
use App\Application\Task\ChangeStatus\TaskChangeStatusCommandHandler;
use App\Application\Task\Create\TaskCreateCommand;
use App\Application\Task\Create\TaskCreateCommandHandler;
use App\Application\Task\List\TaskListQuery;
use App\Application\Task\List\TaskListQueryHandler;
use App\Infrastructure\Persistence\InMemoryTaskRepository;
use PHPUnit\Framework\TestCase;

final class TaskListQueryHandlerTest extends TestCase
{
    private TaskListQueryHandler $handler;
    private TaskCreateCommandHandler $createHandler;
    private TaskChangeStatusCommandHandler $statusHandler;

    /**
     * @throws \Throwable
     */
    #[\Override]
    protected function setUp(): void
    {
        $repository = new InMemoryTaskRepository();
        $this->handler = new TaskListQueryHandler($repository);
        $this->createHandler = new TaskCreateCommandHandler($repository);
        $this->statusHandler = new TaskChangeStatusCommandHandler($repository);
    }

    /**
     * @throws \Throwable
     */
    public function testListEmptyReturnsEmptyArray(): void
    {
        $result = $this->handler->handle(new TaskListQuery());

        static::assertCount(0, $result->unwrap());
    }

    /**
     * @throws \Throwable
     */
    public function testListAllTasks(): void
    {
        $this->createHandler->handle(new TaskCreateCommand(title: 'Task 1'));
        $this->createHandler->handle(new TaskCreateCommand(title: 'Task 2'));
        $this->createHandler->handle(new TaskCreateCommand(title: 'Task 3'));

        $result = $this->handler->handle(new TaskListQuery());

        static::assertCount(3, $result->unwrap());
    }

    /**
     * @throws \Throwable
     */
    public function testListFilteredByStatus(): void
    {
        $task1 = $this->createHandler->handle(new TaskCreateCommand(title: 'Task 1'))->unwrap();
        $this->createHandler->handle(new TaskCreateCommand(title: 'Task 2'));

        $this->statusHandler->handle(new TaskChangeStatusCommand(id: $task1->id->value(), status: 'in_progress'));

        $result = $this->handler->handle(new TaskListQuery(status: 'in_progress'));

        static::assertCount(1, $result->unwrap());
        static::assertSame('Task 1', $result->unwrap()[0]->title->value());
    }
}
