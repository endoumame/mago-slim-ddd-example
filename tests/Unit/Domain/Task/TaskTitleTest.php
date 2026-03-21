<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Task;

use App\Domain\Task\Exception\InvalidTaskTitleException;
use App\Domain\Task\TaskTitle;
use PHPUnit\Framework\TestCase;

final class TaskTitleTest extends TestCase
{
    public function testCreateWithValidTitle(): void
    {
        $result = TaskTitle::create('Buy groceries');

        self::assertTrue($result->isSucceeded());
        self::assertSame('Buy groceries', $result->getResult()->value());
    }

    public function testCreateTrimsWhitespace(): void
    {
        $result = TaskTitle::create('  Buy groceries  ');

        self::assertTrue($result->isSucceeded());
        self::assertSame('Buy groceries', $result->getResult()->value());
    }

    public function testCreateWithEmptyStringFails(): void
    {
        $result = TaskTitle::create('');

        self::assertTrue($result->isFailed());
        self::assertInstanceOf(InvalidTaskTitleException::class, $result->getThrowable());
        self::assertStringContainsString('empty', $result->getThrowable()->getMessage());
    }

    public function testCreateWithWhitespaceOnlyFails(): void
    {
        $result = TaskTitle::create('   ');

        self::assertTrue($result->isFailed());
        self::assertInstanceOf(InvalidTaskTitleException::class, $result->getThrowable());
    }

    public function testCreateWithMaxLengthSucceeds(): void
    {
        $title = str_repeat(string: 'a', times: 255);
        $result = TaskTitle::create($title);

        self::assertTrue($result->isSucceeded());
    }

    public function testCreateWithTooLongTitleFails(): void
    {
        $title = str_repeat(string: 'a', times: 256);
        $result = TaskTitle::create($title);

        self::assertTrue($result->isFailed());
        self::assertInstanceOf(InvalidTaskTitleException::class, $result->getThrowable());
        self::assertStringContainsString('255', $result->getThrowable()->getMessage());
    }

    public function testEquals(): void
    {
        $title1 = TaskTitle::create('Same title')->getResult();
        $title2 = TaskTitle::create('Same title')->getResult();

        self::assertTrue($title1->equals($title2));
    }

    public function testNotEquals(): void
    {
        $title1 = TaskTitle::create('Title A')->getResult();
        $title2 = TaskTitle::create('Title B')->getResult();

        self::assertFalse($title1->equals($title2));
    }
}
