<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Task;

use App\Domain\Task\DoneTask;
use App\Domain\Task\DueDate;
use App\Domain\Task\InProgressTask;
use App\Domain\Task\TaskDescription;
use App\Domain\Task\TaskStatus;
use App\Domain\Task\TaskTitle;
use App\Domain\Task\TodoTask;
use PHPUnit\Framework\TestCase;

final class TaskTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function testCreateTask(): void
    {
        $title = TaskTitle::create('Test task')->getResult();
        $description = TaskDescription::create('A description')->getResult();

        $result = TodoTask::create($title, $description);

        self::assertTrue($result->isSucceeded());

        $task = $result->getResult();
        self::assertInstanceOf(TodoTask::class, $task);
        self::assertSame('Test task', $task->title->value());
        self::assertSame('A description', $task->description->value());
        self::assertSame(TaskStatus::Todo, $task->status);
        self::assertNull($task->dueDate);
    }

    /**
     * @throws \Throwable
     */
    public function testCreateTaskWithDueDate(): void
    {
        $title = TaskTitle::create('Test task')->getResult();
        $description = TaskDescription::empty();
        $futureDate = new \DateTimeImmutable('+7 days')->format('Y-m-d');
        $dueDate = DueDate::create($futureDate)->getResult();

        $result = TodoTask::create($title, $description, $dueDate);

        self::assertTrue($result->isSucceeded());
        $task = $result->getResult();
        $dueDate = $task->dueDate;
        self::assertNotNull($dueDate);
        self::assertSame($futureDate, $dueDate->format());
    }

    /**
     * @throws \Throwable
     */
    public function testChangeTitle(): void
    {
        $task = $this->createTestTask();
        $newTitle = TaskTitle::create('Updated title')->getResult();

        $result = $task->changeTitle($newTitle);

        self::assertTrue($result->isSucceeded());
        self::assertInstanceOf(TodoTask::class, $result->getResult());
        self::assertSame('Updated title', $result->getResult()->title->value());
        self::assertSame($task->id->value(), $result->getResult()->id->value());
    }

    /**
     * @throws \Throwable
     */
    public function testChangeDescription(): void
    {
        $task = $this->createTestTask();
        $newDesc = TaskDescription::create('Updated description')->getResult();

        $result = $task->changeDescription($newDesc);

        self::assertTrue($result->isSucceeded());
        self::assertSame('Updated description', $result->getResult()->description->value());
    }

    /**
     * @throws \Throwable
     */
    public function testStartTransitionsTodoToInProgress(): void
    {
        $task = $this->createTestTask();

        $result = $task->start();

        self::assertTrue($result->isSucceeded());
        self::assertInstanceOf(InProgressTask::class, $result->getResult());
        self::assertSame(TaskStatus::InProgress, $result->getResult()->status);
    }

    /**
     * @throws \Throwable
     */
    public function testCompleteTransitionsInProgressToDone(): void
    {
        $task = $this->createTestTask();
        $inProgressTask = $task->start()->getResult();

        $result = $inProgressTask->complete();

        self::assertTrue($result->isSucceeded());
        self::assertInstanceOf(DoneTask::class, $result->getResult());
        self::assertSame(TaskStatus::Done, $result->getResult()->status);
    }

    /**
     * @throws \Throwable
     */
    public function testInvalidTransitionsPreventedByTypeSystem(): void
    {
        $todoTask = $this->createTestTask();
        $inProgressTask = $todoTask->start()->getResult();
        $doneTask = $inProgressTask->complete()->getResult();

        self::assertFalse(method_exists($todoTask, 'complete'));
        self::assertFalse(method_exists($inProgressTask, 'start'));
        self::assertFalse(method_exists($doneTask, 'start'));
        self::assertFalse(method_exists($doneTask, 'complete'));
    }

    /**
     * @throws \Throwable
     */
    public function testImmutability(): void
    {
        $task = $this->createTestTask();
        $newTitle = TaskTitle::create('New title')->getResult();

        $updatedTask = $task->changeTitle($newTitle)->getResult();

        self::assertSame('Test task', $task->title->value());
        self::assertSame('New title', $updatedTask->title->value());
        self::assertSame($task->id->value(), $updatedTask->id->value());
    }

    /**
     * @throws \Throwable
     */
    public function testChangeTitlePreservesConcreteType(): void
    {
        $todoTask = $this->createTestTask();
        $inProgressTask = $todoTask->start()->getResult();
        $doneTask = $inProgressTask->complete()->getResult();

        $newTitle = TaskTitle::create('New title')->getResult();

        self::assertInstanceOf(TodoTask::class, $todoTask->changeTitle($newTitle)->getResult());
        self::assertInstanceOf(InProgressTask::class, $inProgressTask->changeTitle($newTitle)->getResult());
        self::assertInstanceOf(DoneTask::class, $doneTask->changeTitle($newTitle)->getResult());
    }

    /**
     * @throws \Throwable
     */
    public function testToArray(): void
    {
        $task = $this->createTestTask();
        $array = $task->toArray();

        self::assertArrayHasKey('id', $array);
        self::assertArrayHasKey('title', $array);
        self::assertArrayHasKey('description', $array);
        self::assertArrayHasKey('status', $array);
        self::assertArrayHasKey('due_date', $array);
        self::assertArrayHasKey('created_at', $array);
        self::assertArrayHasKey('updated_at', $array);
        self::assertSame('Test task', $array['title']);
        self::assertSame('todo', $array['status']);
    }

    /**
     * @throws \Throwable
     */
    private function createTestTask(): TodoTask
    {
        $title = TaskTitle::create('Test task')->getResult();
        $description = TaskDescription::empty();

        return TodoTask::create($title, $description)->getResult();
    }
}
