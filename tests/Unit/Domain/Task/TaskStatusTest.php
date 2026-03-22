<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Task;

use App\Domain\Task\TaskStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TaskStatusTest extends TestCase
{
    /**
     * @return iterable<string, array{string, TaskStatus}>
     */
    public static function validStatusValuesProvider(): iterable
    {
        yield 'todo' => ['todo', TaskStatus::Todo];
        yield 'in_progress' => ['in_progress', TaskStatus::InProgress];
        yield 'done' => ['done', TaskStatus::Done];
    }

    /**
     * @throws \Throwable
     */
    #[DataProvider('validStatusValuesProvider')]
    public function testValidStatusFromString(string $value, TaskStatus $expected): void
    {
        $status = TaskStatus::from($value);
        self::assertSame($expected, $status);
    }

    /**
     * @throws \Throwable
     */
    public function testInvalidStatusFromString(): void
    {
        $status = TaskStatus::tryFrom('invalid');
        self::assertNull($status);
    }
}
