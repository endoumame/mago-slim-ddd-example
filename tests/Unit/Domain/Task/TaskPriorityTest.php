<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Task;

use App\Domain\Task\TaskPriority;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TaskPriorityTest extends TestCase
{
    /**
     * @return iterable<string, array{string, TaskPriority}>
     */
    public static function validPriorityValuesProvider(): iterable
    {
        yield 'low' => ['low', TaskPriority::Low];
        yield 'medium' => ['medium', TaskPriority::Medium];
        yield 'high' => ['high', TaskPriority::High];
    }

    /**
     * @throws \Throwable
     */
    #[DataProvider('validPriorityValuesProvider')]
    public function testValidPriorityFromString(string $value, TaskPriority $expected): void
    {
        $priority = TaskPriority::from($value);
        static::assertSame($expected, $priority);
    }

    /**
     * @throws \Throwable
     */
    public function testInvalidPriorityFromString(): void
    {
        $priority = TaskPriority::tryFrom('invalid');
        static::assertNull($priority);
    }
}
