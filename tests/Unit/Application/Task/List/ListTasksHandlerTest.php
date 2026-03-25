<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Task\List;

use App\Application\Task\ChangeStatus\ChangeTaskStatus;
use App\Application\Task\ChangeStatus\ChangeTaskStatusHandler;
use App\Application\Task\Create\CreateTask;
use App\Application\Task\Create\CreateTaskHandler;
use App\Application\Task\List\ListTasks;
use App\Application\Task\List\ListTasksHandler;
use App\Infrastructure\Persistence\InMemoryTaskRepository;
use PHPUnit\Framework\TestCase;

final class ListTasksHandlerTest extends TestCase
{
    private ListTasksHandler $handler;
    private CreateTaskHandler $createHandler;
    private ChangeTaskStatusHandler $statusHandler;

    /**
     * @throws \Throwable
     */
    #[\Override]
    protected function setUp(): void
    {
        $repository = new InMemoryTaskRepository();
        $this->handler = new ListTasksHandler($repository);
        $this->createHandler = new CreateTaskHandler($repository);
        $this->statusHandler = new ChangeTaskStatusHandler($repository);
    }

    /**
     * @throws \Throwable
     */
    public function testListEmptyReturnsEmptyArray(): void
    {
        $result = $this->handler->handle(new ListTasks());

        static::assertCount(0, $result->unwrap());
    }

    /**
     * @throws \Throwable
     */
    public function testListAllTasks(): void
    {
        $this->createHandler->handle(new CreateTask(title: 'Task 1'));
        $this->createHandler->handle(new CreateTask(title: 'Task 2'));
        $this->createHandler->handle(new CreateTask(title: 'Task 3'));

        $result = $this->handler->handle(new ListTasks());

        static::assertCount(3, $result->unwrap());
    }

    /**
     * @throws \Throwable
     */
    public function testListFilteredByStatus(): void
    {
        $task1 = $this->createHandler->handle(new CreateTask(title: 'Task 1'))->unwrap();
        $this->createHandler->handle(new CreateTask(title: 'Task 2'));

        $this->statusHandler->handle(new ChangeTaskStatus(id: $task1->id->value(), status: 'in_progress'));

        $result = $this->handler->handle(new ListTasks(status: 'in_progress'));

        static::assertCount(1, $result->unwrap());
        static::assertSame('Task 1', $result->unwrap()[0]->title->value());
    }
}
