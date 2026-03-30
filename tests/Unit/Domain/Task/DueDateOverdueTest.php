<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Task;

use App\Domain\Task\DueDate;
use PHPUnit\Framework\TestCase;

final class DueDateOverdueTest extends TestCase
{
    // --- reconstitute ---

    /**
     * @throws \Throwable
     */
    public function testReconstituteAcceptsPastDate(): void
    {
        $past = '2020-01-01';
        $dueDate = DueDate::reconstitute($past);

        static::assertSame($past, $dueDate->format());
    }

    /**
     * @throws \Throwable
     */
    public function testReconstituteAcceptsFutureDate(): void
    {
        $future = new \DateTimeImmutable('+30 days')->format('Y-m-d');
        $dueDate = DueDate::reconstitute($future);

        static::assertSame($future, $dueDate->format());
    }

    // --- isOverdue ---

    /**
     * @throws \Throwable
     */
    public function testIsOverdueReturnsTrueWhenDateIsBeforeReference(): void
    {
        $dueDate = DueDate::reconstitute('2025-01-01');
        $reference = new \DateTimeImmutable('2025-01-02');

        static::assertTrue($dueDate->isOverdue($reference));
    }

    /**
     * @throws \Throwable
     */
    public function testIsOverdueReturnsFalseWhenDateEqualsReference(): void
    {
        $dueDate = DueDate::reconstitute('2025-06-15');
        $reference = new \DateTimeImmutable('2025-06-15');

        static::assertFalse($dueDate->isOverdue($reference));
    }

    /**
     * @throws \Throwable
     */
    public function testIsOverdueReturnsFalseWhenDateIsAfterReference(): void
    {
        $dueDate = DueDate::reconstitute('2025-12-31');
        $reference = new \DateTimeImmutable('2025-06-15');

        static::assertFalse($dueDate->isOverdue($reference));
    }
}
