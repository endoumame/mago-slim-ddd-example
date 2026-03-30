<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Task\List;

use App\Application\Task\ChangeStatus\ChangeTaskStatusCommand;
use App\Application\Task\ChangeStatus\ChangeTaskStatusHandler;
use App\Application\Task\Create\CreateTaskCommand;
use App\Application\Task\Create\CreateTaskHandler;
use App\Application\Task\List\ListTasksHandler;
use App\Application\Task\List\ListTasksQuery;
use App\Domain\Task\DueDate;
use App\Domain\Task\Task;
use App\Domain\Task\TaskDescription;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskPriority;
use App\Domain\Task\TaskStatus;
use App\Domain\Task\TaskTitle;
use App\Infrastructure\Persistence\InMemoryTaskRepository;
use PHPUnit\Framework\TestCase;

final class ListTasksHandlerTest extends TestCase
{
    private InMemoryTaskRepository $repository;
    private ListTasksHandler $handler;
    private CreateTaskHandler $createHandler;
    private ChangeTaskStatusHandler $statusHandler;

    /**
     * @throws \Throwable
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->repository = new InMemoryTaskRepository();
        $this->handler = new ListTasksHandler($this->repository);
        $this->createHandler = new CreateTaskHandler($this->repository);
        $this->statusHandler = new ChangeTaskStatusHandler($this->repository);
    }

    /**
     * @throws \Throwable
     */
    public function testListEmptyReturnsEmptyArray(): void
    {
        $result = $this->handler->handle(new ListTasksQuery());

        static::assertCount(0, $result->unwrap());
    }

    /**
     * @throws \Throwable
     */
    public function testListAllTasks(): void
    {
        $this->createHandler->handle(new CreateTaskCommand(title: 'Task 1'));
        $this->createHandler->handle(new CreateTaskCommand(title: 'Task 2'));
        $this->createHandler->handle(new CreateTaskCommand(title: 'Task 3'));

        $result = $this->handler->handle(new ListTasksQuery());

        static::assertCount(3, $result->unwrap());
    }

    /**
     * @throws \Throwable
     */
    public function testListFilteredByStatus(): void
    {
        $task1 = $this->createHandler->handle(new CreateTaskCommand(title: 'Task 1'))->unwrap();
        $this->createHandler->handle(new CreateTaskCommand(title: 'Task 2'));

        $this->statusHandler->handle(new ChangeTaskStatusCommand(id: $task1->id->value(), status: 'in_progress'));

        $result = $this->handler->handle(new ListTasksQuery(status: 'in_progress'));

        static::assertCount(1, $result->unwrap());
        static::assertSame('Task 1', $result->unwrap()[0]->title->value());
    }

    /**
     * @throws \Throwable
     */
    public function testListFilteredByPriority(): void
    {
        $this->createHandler->handle(new CreateTaskCommand(title: 'High task', priority: 'high'));
        $this->createHandler->handle(new CreateTaskCommand(title: 'Low task', priority: 'low'));
        $this->createHandler->handle(new CreateTaskCommand(title: 'Another high', priority: 'high'));

        $result = $this->handler->handle(new ListTasksQuery(priority: 'high'));

        static::assertCount(2, $result->unwrap());
    }

    // --- overdue filter ---

    /**
     * @throws \Throwable
     */
    public function testListFilteredByOverdueTrue(): void
    {
        // Overdue task: past due date, todo status
        $this->saveReconstitutedTask('Overdue task', TaskStatus::Todo, '2020-01-01');
        // Not overdue: future due date
        $this->saveReconstitutedTask('Future task', TaskStatus::Todo, '2099-12-31');
        // Not overdue: done with past due date
        $this->saveReconstitutedTask('Done task', TaskStatus::Done, '2020-01-01');

        $result = $this->handler->handle(new ListTasksQuery(overdue: true));

        static::assertCount(1, $result->unwrap());
        static::assertSame('Overdue task', $result->unwrap()[0]->title->value());
    }

    /**
     * @throws \Throwable
     */
    public function testListFilteredByOverdueFalse(): void
    {
        $this->saveReconstitutedTask('Overdue task', TaskStatus::Todo, '2020-01-01');
        $this->saveReconstitutedTask('Future task', TaskStatus::Todo, '2099-12-31');
        $this->saveReconstitutedTask('No due date', TaskStatus::Todo, null);

        $result = $this->handler->handle(new ListTasksQuery(overdue: false));

        static::assertCount(2, $result->unwrap());
    }

    /**
     * @throws \Throwable
     */
    private function saveReconstitutedTask(
        string $title,
        TaskStatus $status,
        ?string $dueDate,
        TaskPriority $priority = TaskPriority::Medium,
    ): Task {
        $now = new \DateTimeImmutable();
        $task = Task::reconstitute(
            TaskId::generate(),
            TaskTitle::create($title)->unwrap(),
            TaskDescription::empty(),
            $status,
            $priority,
            $dueDate !== null ? DueDate::reconstitute($dueDate) : null,
            $now,
            $now,
        );
        $this->repository->save($task);

        return $task;
    }
}
