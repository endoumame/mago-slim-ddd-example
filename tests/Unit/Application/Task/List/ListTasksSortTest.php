<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Task\List;

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

final class ListTasksSortTest extends TestCase
{
    private InMemoryTaskRepository $repository;
    private ListTasksHandler $handler;

    /**
     * @throws \Throwable
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->repository = new InMemoryTaskRepository();
        $this->handler = new ListTasksHandler($this->repository);
    }

    /**
     * @throws \Throwable
     */
    public function testListSortedByPriorityAscending(): void
    {
        $this->saveReconstitutedTask('High task', TaskStatus::Todo, null, TaskPriority::High);
        $this->saveReconstitutedTask('Low task', TaskStatus::Todo, null, TaskPriority::Low);
        $this->saveReconstitutedTask('Medium task', TaskStatus::Todo, null, TaskPriority::Medium);

        $result = $this->handler->handle(new ListTasksQuery(sortBy: 'priority', sortDirection: 'asc'));
        $tasks = $result->unwrap();

        static::assertSame('Low task', $tasks[0]->title->value());
        static::assertSame('Medium task', $tasks[1]->title->value());
        static::assertSame('High task', $tasks[2]->title->value());
    }

    /**
     * @throws \Throwable
     */
    public function testListSortedByPriorityDescending(): void
    {
        $this->saveReconstitutedTask('High task', TaskStatus::Todo, null, TaskPriority::High);
        $this->saveReconstitutedTask('Low task', TaskStatus::Todo, null, TaskPriority::Low);
        $this->saveReconstitutedTask('Medium task', TaskStatus::Todo, null, TaskPriority::Medium);

        $result = $this->handler->handle(new ListTasksQuery(sortBy: 'priority', sortDirection: 'desc'));
        $tasks = $result->unwrap();

        static::assertSame('High task', $tasks[0]->title->value());
        static::assertSame('Medium task', $tasks[1]->title->value());
        static::assertSame('Low task', $tasks[2]->title->value());
    }

    /**
     * @throws \Throwable
     */
    public function testListSortedByDueDateAscending(): void
    {
        $this->saveReconstitutedTask('Later', TaskStatus::Todo, '2099-12-31');
        $this->saveReconstitutedTask('Sooner', TaskStatus::Todo, '2025-06-01');
        $this->saveReconstitutedTask('No date', TaskStatus::Todo, null);

        $result = $this->handler->handle(new ListTasksQuery(sortBy: 'due_date', sortDirection: 'asc'));
        $tasks = $result->unwrap();

        static::assertSame('Sooner', $tasks[0]->title->value());
        static::assertSame('Later', $tasks[1]->title->value());
        // null due date sorts last in ascending
        static::assertSame('No date', $tasks[2]->title->value());
    }

    /**
     * @throws \Throwable
     */
    public function testListSortedByDueDateDescending(): void
    {
        $this->saveReconstitutedTask('Later', TaskStatus::Todo, '2099-12-31');
        $this->saveReconstitutedTask('Sooner', TaskStatus::Todo, '2025-06-01');
        $this->saveReconstitutedTask('No date', TaskStatus::Todo, null);

        $result = $this->handler->handle(new ListTasksQuery(sortBy: 'due_date', sortDirection: 'desc'));
        $tasks = $result->unwrap();

        static::assertSame('Later', $tasks[0]->title->value());
        static::assertSame('Sooner', $tasks[1]->title->value());
        // null due date sorts last in descending too
        static::assertSame('No date', $tasks[2]->title->value());
    }

    /**
     * @throws \Throwable
     */
    public function testListSortedByCreatedAtAscending(): void
    {
        $now = new \DateTimeImmutable();
        $this->saveReconstitutedTaskWithCreatedAt('Newest', $now->modify('+2 hours'));
        $this->saveReconstitutedTaskWithCreatedAt('Oldest', $now->modify('-1 hour'));
        $this->saveReconstitutedTaskWithCreatedAt('Middle', $now);

        $result = $this->handler->handle(new ListTasksQuery(sortBy: 'created_at', sortDirection: 'asc'));
        $tasks = $result->unwrap();

        static::assertSame('Oldest', $tasks[0]->title->value());
        static::assertSame('Middle', $tasks[1]->title->value());
        static::assertSame('Newest', $tasks[2]->title->value());
    }

    /**
     * @throws \Throwable
     */
    public function testListDefaultSortByIsAscending(): void
    {
        $this->saveReconstitutedTask('High task', TaskStatus::Todo, null, TaskPriority::High);
        $this->saveReconstitutedTask('Low task', TaskStatus::Todo, null, TaskPriority::Low);

        $result = $this->handler->handle(new ListTasksQuery(sortBy: 'priority'));
        $tasks = $result->unwrap();

        static::assertSame('Low task', $tasks[0]->title->value());
        static::assertSame('High task', $tasks[1]->title->value());
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

    /**
     * @throws \Throwable
     */
    private function saveReconstitutedTaskWithCreatedAt(string $title, \DateTimeImmutable $createdAt): Task
    {
        $task = Task::reconstitute(
            TaskId::generate(),
            TaskTitle::create($title)->unwrap(),
            TaskDescription::empty(),
            TaskStatus::Todo,
            TaskPriority::Medium,
            null,
            $createdAt,
            $createdAt,
        );
        $this->repository->save($task);

        return $task;
    }
}
