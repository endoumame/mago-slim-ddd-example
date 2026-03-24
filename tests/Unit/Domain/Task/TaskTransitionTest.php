<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Task;

use App\Domain\Task\DoneTask;
use App\Domain\Task\InProgressTask;
use App\Domain\Task\TaskStatus;
use App\Domain\Task\TaskTitle;
use App\Domain\Task\TodoTask;

final class TaskTransitionTest extends TaskTestCase
{
    /**
     * @throws \Throwable
     */
    public function testStartTransitionsTodoToInProgress(): void
    {
        $task = $this->createTestTask();

        $result = $task->start();

        static::assertInstanceOf(InProgressTask::class, $result->unwrap());
        static::assertSame(TaskStatus::InProgress, $result->unwrap()->status);
    }

    /**
     * @throws \Throwable
     */
    public function testCompleteTransitionsInProgressToDone(): void
    {
        $task = $this->createTestTask();
        $inProgressTask = $task->start()->unwrap();

        $result = $inProgressTask->complete();

        static::assertInstanceOf(DoneTask::class, $result->unwrap());
        static::assertSame(TaskStatus::Done, $result->unwrap()->status);
    }

    /**
     * @throws \Throwable
     */
    public function testInvalidTransitionsPreventedByTypeSystem(): void
    {
        $todoTask = $this->createTestTask();
        $inProgressTask = $todoTask->start()->unwrap();
        $doneTask = $inProgressTask->complete()->unwrap();

        static::assertFalse(\method_exists($todoTask, 'complete'));
        static::assertFalse(\method_exists($inProgressTask, 'start'));
        static::assertFalse(\method_exists($doneTask, 'start'));
        static::assertFalse(\method_exists($doneTask, 'complete'));
    }

    /**
     * @throws \Throwable
     */
    public function testChangeTitlePreservesConcreteType(): void
    {
        $todoTask = $this->createTestTask();
        $inProgressTask = $todoTask->start()->unwrap();
        $doneTask = $inProgressTask->complete()->unwrap();

        $newTitle = TaskTitle::create('New title')->unwrap();

        static::assertInstanceOf(TodoTask::class, $todoTask->changeTitle($newTitle)->unwrap());
        static::assertInstanceOf(InProgressTask::class, $inProgressTask->changeTitle($newTitle)->unwrap());
        static::assertInstanceOf(DoneTask::class, $doneTask->changeTitle($newTitle)->unwrap());
    }
}
