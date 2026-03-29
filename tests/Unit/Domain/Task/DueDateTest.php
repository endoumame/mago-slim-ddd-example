<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Task;

use App\Domain\Task\DueDate;
use App\Domain\Task\Exception\InvalidDueDateException;
use PHPUnit\Framework\TestCase;

final class DueDateTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function testCreateWithTodaySucceeds(): void
    {
        $today = new \DateTimeImmutable('today')->format('Y-m-d');
        $result = DueDate::create($today);

        static::assertSame($today, $result->unwrap()->format());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithFutureDateSucceeds(): void
    {
        $future = new \DateTimeImmutable('+30 days')->format('Y-m-d');
        $result = DueDate::create($future);

        static::assertSame($future, $result->unwrap()->format());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithPastDateFails(): void
    {
        $past = new \DateTimeImmutable('-1 day')->format('Y-m-d');
        $result = DueDate::create($past);

        static::assertInstanceOf(InvalidDueDateException::class, $result->unwrapErr());
        static::assertStringContainsString('past', $result->unwrapErr()->getMessage());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithInvalidFormatFails(): void
    {
        $result = DueDate::create('not-a-date');

        static::assertInstanceOf(InvalidDueDateException::class, $result->unwrapErr());
        static::assertStringContainsString('format', $result->unwrapErr()->getMessage());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithInvalidDateValueFails(): void
    {
        $result = DueDate::create('2025-13-45');

        static::assertInstanceOf(InvalidDueDateException::class, $result->unwrapErr());
    }

    /**
     * @throws \Throwable
     */
    public function testEquals(): void
    {
        $date = new \DateTimeImmutable('+5 days')->format('Y-m-d');
        $d1 = DueDate::create($date)->unwrap();
        $d2 = DueDate::create($date)->unwrap();

        static::assertTrue($d1->equals($d2));
    }

    /**
     * @throws \Throwable
     */
    public function testToString(): void
    {
        $date = new \DateTimeImmutable('+5 days')->format('Y-m-d');
        $dueDate = DueDate::create($date)->unwrap();

        static::assertSame($date, (string) $dueDate);
    }

    // --- reconstitute ---

    public function testReconstituteAcceptsPastDate(): void
    {
        $past = '2020-01-01';
        $dueDate = DueDate::reconstitute($past);

        static::assertSame($past, $dueDate->format());
    }

    public function testReconstituteAcceptsFutureDate(): void
    {
        $future = new \DateTimeImmutable('+30 days')->format('Y-m-d');
        $dueDate = DueDate::reconstitute($future);

        static::assertSame($future, $dueDate->format());
    }

    // --- isOverdue ---

    public function testIsOverdueReturnsTrueWhenDateIsBeforeReference(): void
    {
        $dueDate = DueDate::reconstitute('2025-01-01');
        $reference = new \DateTimeImmutable('2025-01-02');

        static::assertTrue($dueDate->isOverdue($reference));
    }

    public function testIsOverdueReturnsFalseWhenDateEqualsReference(): void
    {
        $dueDate = DueDate::reconstitute('2025-06-15');
        $reference = new \DateTimeImmutable('2025-06-15');

        static::assertFalse($dueDate->isOverdue($reference));
    }

    public function testIsOverdueReturnsFalseWhenDateIsAfterReference(): void
    {
        $dueDate = DueDate::reconstitute('2025-12-31');
        $reference = new \DateTimeImmutable('2025-06-15');

        static::assertFalse($dueDate->isOverdue($reference));
    }
}
