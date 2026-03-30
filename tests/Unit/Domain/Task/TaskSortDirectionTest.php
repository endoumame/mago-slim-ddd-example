<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Task;

use App\Domain\Task\TaskSortDirection;
use PHPUnit\Framework\TestCase;

final class TaskSortDirectionTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function testTryFromWithValidValues(): void
    {
        static::assertSame(TaskSortDirection::Asc, TaskSortDirection::tryFrom('asc'));
        static::assertSame(TaskSortDirection::Desc, TaskSortDirection::tryFrom('desc'));
    }

    /**
     * @throws \Throwable
     */
    public function testTryFromWithInvalidValueReturnsNull(): void
    {
        static::assertNull(TaskSortDirection::tryFrom('invalid'));
    }
}
