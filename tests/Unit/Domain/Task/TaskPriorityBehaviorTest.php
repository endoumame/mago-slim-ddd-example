<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Task;

use App\Domain\Task\TaskDescription;
use App\Domain\Task\TaskPriority;
use App\Domain\Task\TaskTitle;
use App\Domain\Task\TodoTask;

final class TaskPriorityBehaviorTest extends TaskTestCase
{
    /**
     * @throws \Throwable
     */
    public function testCreateTaskHasDefaultPriority(): void
    {
        $task = $this->createTestTask();

        static::assertSame(TaskPriority::Medium, $task->priority);
    }

    /**
     * @throws \Throwable
     */
    public function testCreateTaskWithPriority(): void
    {
        $title = TaskTitle::create('Urgent task')->unwrap();
        $description = TaskDescription::empty();

        $task = TodoTask::create($title, $description, priority: TaskPriority::High)->unwrap();

        static::assertSame(TaskPriority::High, $task->priority);
    }

    /**
     * @throws \Throwable
     */
    public function testChangePriority(): void
    {
        $task = $this->createTestTask();

        $result = $task->changePriority(TaskPriority::High);

        $updated = $result->unwrap();
        static::assertInstanceOf(TodoTask::class, $updated);
        static::assertSame(TaskPriority::High, $updated->priority);
        static::assertSame($task->id->value(), $updated->id->value());
    }

    /**
     * @throws \Throwable
     */
    public function testChangePriorityPreservesImmutability(): void
    {
        $task = $this->createTestTask();

        $updatedTask = $task->changePriority(TaskPriority::Low)->unwrap();

        static::assertSame(TaskPriority::Medium, $task->priority);
        static::assertSame(TaskPriority::Low, $updatedTask->priority);
    }

    /**
     * @throws \Throwable
     */
    public function testToArrayIncludesPriority(): void
    {
        $task = $this->createTestTask();
        $array = $task->toArray();

        static::assertArrayHasKey('priority', $array);
        static::assertSame('medium', $array['priority']);
    }
}
