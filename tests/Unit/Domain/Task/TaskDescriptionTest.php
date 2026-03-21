<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Task;

use App\Domain\Task\Exception\InvalidTaskDescriptionException;
use App\Domain\Task\TaskDescription;
use PHPUnit\Framework\TestCase;

final class TaskDescriptionTest extends TestCase
{
    public function testCreateWithValidDescription(): void
    {
        $result = TaskDescription::create('A detailed task description');

        self::assertTrue($result->isSucceeded());
        self::assertSame('A detailed task description', $result->getResult()->value());
    }

    public function testCreateWithEmptyStringSucceeds(): void
    {
        $result = TaskDescription::create('');

        self::assertTrue($result->isSucceeded());
        self::assertSame('', $result->getResult()->value());
    }

    public function testCreateTrimsWhitespace(): void
    {
        $result = TaskDescription::create('  description  ');

        self::assertTrue($result->isSucceeded());
        self::assertSame('description', $result->getResult()->value());
    }

    public function testCreateWithMaxLengthSucceeds(): void
    {
        $desc = str_repeat(string: 'a', times: 1000);
        $result = TaskDescription::create($desc);

        self::assertTrue($result->isSucceeded());
    }

    public function testCreateWithTooLongDescriptionFails(): void
    {
        $desc = str_repeat(string: 'a', times: 1001);
        $result = TaskDescription::create($desc);

        self::assertTrue($result->isFailed());
        self::assertInstanceOf(InvalidTaskDescriptionException::class, $result->getThrowable());
        self::assertStringContainsString('1000', $result->getThrowable()->getMessage());
    }

    public function testEmptyFactory(): void
    {
        $desc = TaskDescription::empty();

        self::assertSame('', $desc->value());
    }

    public function testEquals(): void
    {
        $desc1 = TaskDescription::create('Same')->getResult();
        $desc2 = TaskDescription::create('Same')->getResult();

        self::assertTrue($desc1->equals($desc2));
    }
}
