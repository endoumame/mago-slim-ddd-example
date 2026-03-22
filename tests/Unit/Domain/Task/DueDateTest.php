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

        self::assertTrue($result->isSucceeded());
        self::assertSame($today, $result->getResult()->format());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithFutureDateSucceeds(): void
    {
        $future = new \DateTimeImmutable('+30 days')->format('Y-m-d');
        $result = DueDate::create($future);

        self::assertTrue($result->isSucceeded());
        self::assertSame($future, $result->getResult()->format());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithPastDateFails(): void
    {
        $past = new \DateTimeImmutable('-1 day')->format('Y-m-d');
        $result = DueDate::create($past);

        self::assertTrue($result->isFailed());
        self::assertInstanceOf(InvalidDueDateException::class, $result->getThrowable());
        self::assertStringContainsString('past', $result->getThrowable()->getMessage());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithInvalidFormatFails(): void
    {
        $result = DueDate::create('not-a-date');

        self::assertTrue($result->isFailed());
        self::assertInstanceOf(InvalidDueDateException::class, $result->getThrowable());
        self::assertStringContainsString('format', $result->getThrowable()->getMessage());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithInvalidDateValueFails(): void
    {
        $result = DueDate::create('2025-13-45');

        self::assertTrue($result->isFailed());
        self::assertInstanceOf(InvalidDueDateException::class, $result->getThrowable());
    }

    /**
     * @throws \Throwable
     */
    public function testEquals(): void
    {
        $date = new \DateTimeImmutable('+5 days')->format('Y-m-d');
        $d1 = DueDate::create($date)->getResult();
        $d2 = DueDate::create($date)->getResult();

        self::assertTrue($d1->equals($d2));
    }

    /**
     * @throws \Throwable
     */
    public function testToString(): void
    {
        $date = new \DateTimeImmutable('+5 days')->format('Y-m-d');
        $dueDate = DueDate::create($date)->getResult();

        self::assertSame($date, (string) $dueDate);
    }
}
