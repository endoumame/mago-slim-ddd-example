<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Task;

use App\Domain\Task\DueDate;
use App\Domain\Task\Task;
use App\Domain\Task\TaskDescription;
use App\Domain\Task\TaskPriority;
use App\Domain\Task\TaskStatus;
use App\Domain\Task\TaskTitle;
use App\Domain\Task\TodoTask;

final class TaskBehaviorTest extends TaskTestCase
{
    /**
     * @throws \Throwable
     */
    public function testCreateTask(): void
    {
        $title = TaskTitle::create('Test task')->unwrap();
        $description = TaskDescription::create('A description')->unwrap();

        $result = TodoTask::create($title, $description);

        $task = $result->unwrap();
        static::assertInstanceOf(TodoTask::class, $task);
        static::assertSame('Test task', $task->title->value());
        static::assertSame('A description', $task->description->value());
        static::assertSame(TaskStatus::Todo, $task->status);
        static::assertNull($task->dueDate);
    }

    /**
     * @throws \Throwable
     */
    public function testCreateTaskWithDueDate(): void
    {
        $title = TaskTitle::create('Test task')->unwrap();
        $description = TaskDescription::empty();
        $futureDate = new \DateTimeImmutable('+7 days')->format('Y-m-d');
        $dueDate = DueDate::create($futureDate)->unwrap();

        $result = TodoTask::create($title, $description, $dueDate);

        $task = $result->unwrap();
        $dueDate = $task->dueDate;
        static::assertNotNull($dueDate);
        static::assertSame($futureDate, $dueDate->format());
    }

    /**
     * @throws \Throwable
     */
    public function testChangeTitle(): void
    {
        $task = $this->createTestTask();
        $newTitle = TaskTitle::create('Updated title')->unwrap();

        $result = $task->changeTitle($newTitle);

        static::assertInstanceOf(TodoTask::class, $result->unwrap());
        static::assertSame('Updated title', $result->unwrap()->title->value());
        static::assertSame($task->id->value(), $result->unwrap()->id->value());
    }

    /**
     * @throws \Throwable
     */
    public function testChangeDescription(): void
    {
        $task = $this->createTestTask();
        $newDesc = TaskDescription::create('Updated description')->unwrap();

        $result = $task->changeDescription($newDesc);

        static::assertSame('Updated description', $result->unwrap()->description->value());
    }

    /**
     * @throws \Throwable
     */
    public function testImmutability(): void
    {
        $task = $this->createTestTask();
        $newTitle = TaskTitle::create('New title')->unwrap();

        $updatedTask = $task->changeTitle($newTitle)->unwrap();

        static::assertSame('Test task', $task->title->value());
        static::assertSame('New title', $updatedTask->title->value());
        static::assertSame($task->id->value(), $updatedTask->id->value());
    }

    /**
     * @throws \Throwable
     */
    public function testToArray(): void
    {
        $task = $this->createTestTask();
        $array = $task->toArray();

        static::assertArrayHasKey('id', $array);
        static::assertArrayHasKey('title', $array);
        static::assertArrayHasKey('description', $array);
        static::assertArrayHasKey('status', $array);
        static::assertArrayHasKey('due_date', $array);
        static::assertArrayHasKey('created_at', $array);
        static::assertArrayHasKey('updated_at', $array);
        static::assertArrayHasKey('is_overdue', $array);
        static::assertSame('Test task', $array['title']);
        static::assertSame('todo', $array['status']);
        static::assertFalse($array['is_overdue']);
    }

    // --- isOverdue ---

    /**
     * @throws \Throwable
     */
    public function testTodoTaskWithPastDueDateIsOverdue(): void
    {
        $task = $this->createTaskWithDueDate(TaskStatus::Todo, '2020-01-01');

        static::assertTrue($task->isOverdue(new \DateTimeImmutable('2025-06-01')));
    }

    /**
     * @throws \Throwable
     */
    public function testInProgressTaskWithPastDueDateIsOverdue(): void
    {
        $task = $this->createTaskWithDueDate(TaskStatus::InProgress, '2020-01-01');

        static::assertTrue($task->isOverdue(new \DateTimeImmutable('2025-06-01')));
    }

    /**
     * @throws \Throwable
     */
    public function testDoneTaskWithPastDueDateIsNotOverdue(): void
    {
        $task = $this->createTaskWithDueDate(TaskStatus::Done, '2020-01-01');

        static::assertFalse($task->isOverdue(new \DateTimeImmutable('2025-06-01')));
    }

    /**
     * @throws \Throwable
     */
    public function testTaskWithNoDueDateIsNotOverdue(): void
    {
        $task = $this->createTestTask();

        static::assertFalse($task->isOverdue(new \DateTimeImmutable('2025-06-01')));
    }

    /**
     * @throws \Throwable
     */
    public function testTaskWithFutureDueDateIsNotOverdue(): void
    {
        $task = $this->createTaskWithDueDate(TaskStatus::Todo, '2099-12-31');

        static::assertFalse($task->isOverdue(new \DateTimeImmutable('2025-06-01')));
    }

    /**
     * @throws \Throwable
     */
    private function createTaskWithDueDate(TaskStatus $status, string $dueDateStr): Task
    {
        $title = TaskTitle::create('Test task')->unwrap();
        $description = TaskDescription::empty();
        $dueDate = DueDate::reconstitute($dueDateStr);

        return Task::reconstitute(
            \App\Domain\Task\TaskId::generate(),
            $title,
            $description,
            $status,
            TaskPriority::Medium,
            $dueDate,
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
        );
    }
}
