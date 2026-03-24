<?php

declare(strict_types=1);

namespace App\Tests\Property\Domain\Task;

use App\Domain\Task\DueDate;
use App\Domain\Task\TaskDescription;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskTitle;
use App\Tests\Support\PropertyTestCase;

final class ValueObjectPropertyTest extends PropertyTestCase
{
    /**
     * @throws \Throwable
     */
    public function testAnyNonEmptyStringUpTo255CharsCreatesValidTaskTitle(): void
    {
        $this->forAll(self::suchThat(
            static fn(string $s): bool => \trim($s) !== '' && \mb_strlen(\trim($s)) <= 255,
            self::string(),
        ))->then(static function (string $value): void {
            $result = TaskTitle::create($value);
            self::assertSame(\trim($value), $result->unwrap()->value());
        });
    }

    /**
     * @throws \Throwable
     */
    public function testAnyStringOver255CharsFailsTaskTitle(): void
    {
        $this->forAll(self::map(
            static fn(int $extra): string => \str_repeat(string: 'a', times: 256 + $extra),
            self::choose(0, 100),
        ))->then(static function (string $value): void {
            $result = TaskTitle::create($value);
            self::assertInstanceOf(\Throwable::class, $result->unwrapErr());
        });
    }

    /**
     * @throws \Throwable
     */
    public function testEmptyStringAlwaysFailsTaskTitle(): void
    {
        $this->forAll(self::constant(''))->then(static function (string $value): void {
            $result = TaskTitle::create($value);
            self::assertInstanceOf(\Throwable::class, $result->unwrapErr());
        });
    }

    /**
     * @throws \Throwable
     */
    public function testAnyStringUpTo1000CharsCreatesValidTaskDescription(): void
    {
        $this->forAll(self::suchThat(
            static fn(string $s): bool => \mb_strlen(\trim($s)) <= 1000,
            self::string(),
        ))->then(static function (string $value): void {
            $result = TaskDescription::create($value);
            self::assertNotNull($result->unwrap());
        });
    }

    /**
     * @throws \Throwable
     */
    public function testAnyStringOver1000CharsFailsTaskDescription(): void
    {
        $this->forAll(self::map(
            static fn(int $extra): string => \str_repeat(string: 'x', times: 1001 + $extra),
            self::choose(0, 100),
        ))->then(static function (string $value): void {
            $result = TaskDescription::create($value);
            self::assertInstanceOf(\Throwable::class, $result->unwrapErr());
        });
    }

    /**
     * @throws \Throwable
     */
    public function testTaskIdRoundTrips(): void
    {
        $this->forAll(self::map(
            static fn(int $_): string => TaskId::generate()->value(),
            self::choose(0, 100),
        ))->then(static function (string $uuid): void {
            $result = TaskId::create($uuid);
            self::assertSame($uuid, $result->unwrap()->value());
            self::assertSame($uuid, (string) $result->unwrap());
        });
    }

    /**
     * @throws \Throwable
     */
    public function testFutureDatesAlwaysCreateValidDueDate(): void
    {
        $this->forAll(self::choose(0, 365))->then(static function (int $daysFromNow): void {
            $date = new \DateTimeImmutable("+{$daysFromNow} days")->format('Y-m-d');
            $result = DueDate::create($date);
            self::assertNotNull($result->unwrap());
        });
    }

    /**
     * @throws \Throwable
     */
    public function testPastDatesAlwaysFailDueDate(): void
    {
        $this->forAll(self::choose(1, 365))->then(static function (int $daysAgo): void {
            $date = new \DateTimeImmutable("-{$daysAgo} days")->format('Y-m-d');
            $result = DueDate::create($date);
            self::assertInstanceOf(\Throwable::class, $result->unwrapErr());
        });
    }
}
