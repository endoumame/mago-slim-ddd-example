<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Task;

use App\Domain\Task\DueDate;
use App\Domain\Task\Exception\InvalidTaskStatusTransitionException;
use App\Domain\Task\Task;
use App\Domain\Task\TaskDescription;
use App\Domain\Task\TaskStatus;
use App\Domain\Task\TaskTitle;
use PHPUnit\Framework\TestCase;

final class TaskTest extends TestCase
{
    public function testCreateTask(): void
    {
        $title = TaskTitle::create('Test task')->getResult();
        $description = TaskDescription::create('A description')->getResult();

        $result = Task::create($title, $description);

        self::assertTrue($result->isSucceeded());

        $task = $result->getResult();
        self::assertSame('Test task', $task->title->value());
        self::assertSame('A description', $task->description->value());
        self::assertSame(TaskStatus::Todo, $task->status);
        self::assertNull($task->dueDate);
    }

    public function testCreateTaskWithDueDate(): void
    {
        $title = TaskTitle::create('Test task')->getResult();
        $description = TaskDescription::empty();
        $futureDate = new \DateTimeImmutable('+7 days')->format('Y-m-d');
        $dueDate = DueDate::create($futureDate)->getResult();

        $result = Task::create($title, $description, $dueDate);

        self::assertTrue($result->isSucceeded());
        self::assertNotNull($result->getResult()->dueDate);
        self::assertSame($futureDate, $result->getResult()->dueDate->format());
    }

    public function testChangeTitle(): void
    {
        $task = $this->createTestTask();
        $newTitle = TaskTitle::create('Updated title')->getResult();

        $result = $task->changeTitle($newTitle);

        self::assertTrue($result->isSucceeded());
        self::assertSame('Updated title', $result->getResult()->title->value());
        self::assertSame($task->id->value(), $result->getResult()->id->value());
    }

    public function testChangeDescription(): void
    {
        $task = $this->createTestTask();
        $newDesc = TaskDescription::create('Updated description')->getResult();

        $result = $task->changeDescription($newDesc);

        self::assertTrue($result->isSucceeded());
        self::assertSame('Updated description', $result->getResult()->description->value());
    }

    public function testChangeStatusTodoToInProgress(): void
    {
        $task = $this->createTestTask();

        $result = $task->changeStatus(TaskStatus::InProgress);

        self::assertTrue($result->isSucceeded());
        self::assertSame(TaskStatus::InProgress, $result->getResult()->status);
    }

    public function testChangeStatusInProgressToDone(): void
    {
        $task = $this->createTestTask();
        $inProgressTask = $task->changeStatus(TaskStatus::InProgress)->getResult();

        $result = $inProgressTask->changeStatus(TaskStatus::Done);

        self::assertTrue($result->isSucceeded());
        self::assertSame(TaskStatus::Done, $result->getResult()->status);
    }

    public function testChangeStatusInvalidTransitionFails(): void
    {
        $task = $this->createTestTask();

        $result = $task->changeStatus(TaskStatus::Done);

        self::assertTrue($result->isFailed());
        self::assertInstanceOf(InvalidTaskStatusTransitionException::class, $result->getThrowable());
    }

    public function testImmutability(): void
    {
        $task = $this->createTestTask();
        $newTitle = TaskTitle::create('New title')->getResult();

        $updatedTask = $task->changeTitle($newTitle)->getResult();

        self::assertSame('Test task', $task->title->value());
        self::assertSame('New title', $updatedTask->title->value());
        self::assertSame($task->id->value(), $updatedTask->id->value());
    }

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

    private function createTestTask(): Task
    {
        $title = TaskTitle::create('Test task')->getResult();
        $description = TaskDescription::empty();

        return Task::create($title, $description)->getResult();
    }
}
