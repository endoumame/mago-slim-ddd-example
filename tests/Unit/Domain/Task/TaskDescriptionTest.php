<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Task;

use App\Domain\Task\Exception\InvalidTaskDescriptionException;
use App\Domain\Task\TaskDescription;
use PHPUnit\Framework\TestCase;

final class TaskDescriptionTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function testCreateWithValidDescription(): void
    {
        $result = TaskDescription::create('A detailed task description');

        self::assertTrue($result->isSucceeded());
        self::assertSame('A detailed task description', $result->getResult()->value());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithEmptyStringSucceeds(): void
    {
        $result = TaskDescription::create('');

        self::assertTrue($result->isSucceeded());
        self::assertSame('', $result->getResult()->value());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateTrimsWhitespace(): void
    {
        $result = TaskDescription::create('  description  ');

        self::assertTrue($result->isSucceeded());
        self::assertSame('description', $result->getResult()->value());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithMaxLengthSucceeds(): void
    {
        $desc = str_repeat(string: 'a', times: 1000);
        $result = TaskDescription::create($desc);

        self::assertTrue($result->isSucceeded());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithTooLongDescriptionFails(): void
    {
        $desc = str_repeat(string: 'a', times: 1001);
        $result = TaskDescription::create($desc);

        self::assertTrue($result->isFailed());
        self::assertInstanceOf(InvalidTaskDescriptionException::class, $result->getThrowable());
        self::assertStringContainsString('1000', $result->getThrowable()->getMessage());
    }

    /**
     * @throws \Throwable
     */
    public function testEmptyFactory(): void
    {
        $desc = TaskDescription::empty();

        self::assertSame('', $desc->value());
    }

    /**
     * @throws \Throwable
     */
    public function testEquals(): void
    {
        $desc1 = TaskDescription::create('Same')->getResult();
        $desc2 = TaskDescription::create('Same')->getResult();

        self::assertTrue($desc1->equals($desc2));
    }
}
