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

        self::assertTrue($result->isOk());
        self::assertSame($today, $result->unwrap()->format());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithFutureDateSucceeds(): void
    {
        $future = new \DateTimeImmutable('+30 days')->format('Y-m-d');
        $result = DueDate::create($future);

        self::assertTrue($result->isOk());
        self::assertSame($future, $result->unwrap()->format());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithPastDateFails(): void
    {
        $past = new \DateTimeImmutable('-1 day')->format('Y-m-d');
        $result = DueDate::create($past);

        self::assertTrue($result->isErr());
        self::assertInstanceOf(InvalidDueDateException::class, $result->unwrapErr());
        self::assertStringContainsString('past', $result->unwrapErr()->getMessage());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithInvalidFormatFails(): void
    {
        $result = DueDate::create('not-a-date');

        self::assertTrue($result->isErr());
        self::assertInstanceOf(InvalidDueDateException::class, $result->unwrapErr());
        self::assertStringContainsString('format', $result->unwrapErr()->getMessage());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithInvalidDateValueFails(): void
    {
        $result = DueDate::create('2025-13-45');

        self::assertTrue($result->isErr());
        self::assertInstanceOf(InvalidDueDateException::class, $result->unwrapErr());
    }

    /**
     * @throws \Throwable
     */
    public function testEquals(): void
    {
        $date = new \DateTimeImmutable('+5 days')->format('Y-m-d');
        $d1 = DueDate::create($date)->unwrap();
        $d2 = DueDate::create($date)->unwrap();

        self::assertTrue($d1->equals($d2));
    }

    /**
     * @throws \Throwable
     */
    public function testToString(): void
    {
        $date = new \DateTimeImmutable('+5 days')->format('Y-m-d');
        $dueDate = DueDate::create($date)->unwrap();

        self::assertSame($date, (string) $dueDate);
    }
}
