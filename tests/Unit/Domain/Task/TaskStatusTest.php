<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Task;

use App\Domain\Task\Exception\InvalidTaskStatusTransitionException;
use App\Domain\Task\TaskStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TaskStatusTest extends TestCase
{
    /**
     * @return iterable<string, array{TaskStatus, TaskStatus}>
     */
    public static function validTransitionsProvider(): iterable
    {
        yield 'todo to in_progress' => [TaskStatus::Todo, TaskStatus::InProgress];
        yield 'in_progress to done' => [TaskStatus::InProgress, TaskStatus::Done];
    }

    /**
     * @return iterable<string, array{TaskStatus, TaskStatus}>
     */
    public static function invalidTransitionsProvider(): iterable
    {
        yield 'todo to done' => [TaskStatus::Todo, TaskStatus::Done];
        yield 'todo to todo' => [TaskStatus::Todo, TaskStatus::Todo];
        yield 'in_progress to todo' => [TaskStatus::InProgress, TaskStatus::Todo];
        yield 'in_progress to in_progress' => [TaskStatus::InProgress, TaskStatus::InProgress];
        yield 'done to todo' => [TaskStatus::Done, TaskStatus::Todo];
        yield 'done to in_progress' => [TaskStatus::Done, TaskStatus::InProgress];
        yield 'done to done' => [TaskStatus::Done, TaskStatus::Done];
    }

    /**
     * @throws \Throwable
     */
    #[DataProvider('validTransitionsProvider')]
    public function testValidTransition(TaskStatus $from, TaskStatus $to): void
    {
        $result = $from->transitionTo($to);

        self::assertTrue($result->isSucceeded());
        self::assertSame($to, $result->getResult());
    }

    /**
     * @throws \Throwable
     */
    #[DataProvider('invalidTransitionsProvider')]
    public function testInvalidTransition(TaskStatus $from, TaskStatus $to): void
    {
        $result = $from->transitionTo($to);

        self::assertTrue($result->isFailed());
        self::assertInstanceOf(InvalidTaskStatusTransitionException::class, $result->getThrowable());
    }
}
