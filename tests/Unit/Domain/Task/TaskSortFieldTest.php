<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Task;

use App\Domain\Task\TaskSortField;
use PHPUnit\Framework\TestCase;

final class TaskSortFieldTest extends TestCase
{
    public function testTryFromWithValidValues(): void
    {
        static::assertSame(TaskSortField::Priority, TaskSortField::tryFrom('priority'));
        static::assertSame(TaskSortField::DueDate, TaskSortField::tryFrom('due_date'));
        static::assertSame(TaskSortField::CreatedAt, TaskSortField::tryFrom('created_at'));
    }

    public function testTryFromWithInvalidValueReturnsNull(): void
    {
        static::assertNull(TaskSortField::tryFrom('invalid'));
    }
}
