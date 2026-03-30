<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Task;

use App\Domain\Task\DueDate;
use App\Domain\Task\Task;
use App\Domain\Task\TaskDescription;
use App\Domain\Task\TaskPriority;
use App\Domain\Task\TaskStatus;
use App\Domain\Task\TaskTitle;

final class TaskOverdueTest extends TaskTestCase
{
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
