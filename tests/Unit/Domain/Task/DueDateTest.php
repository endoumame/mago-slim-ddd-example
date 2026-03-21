<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Task;

use App\Domain\Task\DueDate;
use App\Domain\Task\Exception\InvalidDueDateException;
use PHPUnit\Framework\TestCase;

final class DueDateTest extends TestCase
{
    public function testCreateWithTodaySucceeds(): void
    {
        $today = new \DateTimeImmutable('today')->format('Y-m-d');
        $result = DueDate::create($today);

        self::assertTrue($result->isSucceeded());
        self::assertSame($today, $result->getResult()->format());
    }

    public function testCreateWithFutureDateSucceeds(): void
    {
        $future = new \DateTimeImmutable('+30 days')->format('Y-m-d');
        $result = DueDate::create($future);

        self::assertTrue($result->isSucceeded());
        self::assertSame($future, $result->getResult()->format());
    }

    public function testCreateWithPastDateFails(): void
    {
        $past = new \DateTimeImmutable('-1 day')->format('Y-m-d');
        $result = DueDate::create($past);

        self::assertTrue($result->isFailed());
        self::assertInstanceOf(InvalidDueDateException::class, $result->getThrowable());
        self::assertStringContainsString('past', $result->getThrowable()->getMessage());
    }

    public function testCreateWithInvalidFormatFails(): void
    {
        $result = DueDate::create('not-a-date');

        self::assertTrue($result->isFailed());
        self::assertInstanceOf(InvalidDueDateException::class, $result->getThrowable());
        self::assertStringContainsString('format', $result->getThrowable()->getMessage());
    }

    public function testCreateWithInvalidDateValueFails(): void
    {
        $result = DueDate::create('2025-13-45');

        self::assertTrue($result->isFailed());
        self::assertInstanceOf(InvalidDueDateException::class, $result->getThrowable());
    }

    public function testEquals(): void
    {
        $date = new \DateTimeImmutable('+5 days')->format('Y-m-d');
        $d1 = DueDate::create($date)->getResult();
        $d2 = DueDate::create($date)->getResult();

        self::assertTrue($d1->equals($d2));
    }

    public function testToString(): void
    {
        $date = new \DateTimeImmutable('+5 days')->format('Y-m-d');
        $dueDate = DueDate::create($date)->getResult();

        self::assertSame($date, (string) $dueDate);
    }
}
