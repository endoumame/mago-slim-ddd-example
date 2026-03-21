<?php

declare(strict_types=1);

namespace App\Tests\Property\Domain\Task;

use App\Domain\Task\DueDate;
use App\Domain\Task\TaskDescription;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskTitle;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

use function Eris\Generator\choose;
use function Eris\Generator\constant;
use function Eris\Generator\map;
use function Eris\Generator\string;
use function Eris\Generator\suchThat;

/** @mago-ignore too-many-methods */
final class ValueObjectPropertyTest extends TestCase
{
    use TestTrait;

    public function testAnyNonEmptyStringUpTo255CharsCreatesValidTaskTitle(): void
    {
        $this->forAll(suchThat(
            static fn(string $s): bool => trim($s) !== '' && mb_strlen(trim($s)) <= 255,
            string(),
        ))->then(static function (string $value): void {
            $result = TaskTitle::create($value);
            self::assertTrue($result->isSucceeded(), "Expected success for: '{$value}'");
            self::assertSame(trim($value), $result->getResult()->value());
        });
    }

    public function testAnyStringOver255CharsFailsTaskTitle(): void
    {
        $this->forAll(map(
            static fn(int $extra): string => str_repeat(string: 'a', times: 256 + $extra),
            choose(0, 100),
        ))->then(static function (string $value): void {
            $result = TaskTitle::create($value);
            self::assertTrue($result->isFailed());
        });
    }

    public function testEmptyStringAlwaysFailsTaskTitle(): void
    {
        $this->forAll(constant(''))->then(static function (string $value): void {
            $result = TaskTitle::create($value);
            self::assertTrue($result->isFailed());
        });
    }

    public function testAnyStringUpTo1000CharsCreatesValidTaskDescription(): void
    {
        $this->forAll(suchThat(
            static fn(string $s): bool => mb_strlen(trim($s)) <= 1000,
            string(),
        ))->then(static function (string $value): void {
            $result = TaskDescription::create($value);
            self::assertTrue($result->isSucceeded());
        });
    }

    public function testAnyStringOver1000CharsFailsTaskDescription(): void
    {
        $this->forAll(map(
            static fn(int $extra): string => str_repeat(string: 'x', times: 1001 + $extra),
            choose(0, 100),
        ))->then(static function (string $value): void {
            $result = TaskDescription::create($value);
            self::assertTrue($result->isFailed());
        });
    }

    public function testTaskIdRoundTrips(): void
    {
        $this->forAll(map(
            static fn(int $_): string => TaskId::generate()->value(),
            choose(0, 100),
        ))->then(static function (string $uuid): void {
            $result = TaskId::create($uuid);
            self::assertTrue($result->isSucceeded());
            self::assertSame($uuid, $result->getResult()->value());
            self::assertSame($uuid, (string) $result->getResult());
        });
    }

    public function testFutureDatesAlwaysCreateValidDueDate(): void
    {
        $this->forAll(choose(0, 365))->then(static function (int $daysFromNow): void {
            $date = new \DateTimeImmutable("+{$daysFromNow} days")->format('Y-m-d');
            $result = DueDate::create($date);
            self::assertTrue($result->isSucceeded(), "Expected success for: {$date}");
        });
    }

    public function testPastDatesAlwaysFailDueDate(): void
    {
        $this->forAll(choose(1, 365))->then(static function (int $daysAgo): void {
            $date = new \DateTimeImmutable("-{$daysAgo} days")->format('Y-m-d');
            $result = DueDate::create($date);
            self::assertTrue($result->isFailed(), "Expected failure for past date: {$date}");
        });
    }
}
