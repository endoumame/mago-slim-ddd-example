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

        self::assertSame('A detailed task description', $result->unwrap()->value());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithEmptyStringSucceeds(): void
    {
        $result = TaskDescription::create('');

        self::assertSame('', $result->unwrap()->value());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateTrimsWhitespace(): void
    {
        $result = TaskDescription::create('  description  ');

        self::assertSame('description', $result->unwrap()->value());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithMaxLengthSucceeds(): void
    {
        $desc = str_repeat(string: 'a', times: 1000);
        $result = TaskDescription::create($desc);

        self::assertNotNull($result->unwrap());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithTooLongDescriptionFails(): void
    {
        $desc = str_repeat(string: 'a', times: 1001);
        $result = TaskDescription::create($desc);

        self::assertInstanceOf(InvalidTaskDescriptionException::class, $result->unwrapErr());
        self::assertStringContainsString('1000', $result->unwrapErr()->getMessage());
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
        $desc1 = TaskDescription::create('Same')->unwrap();
        $desc2 = TaskDescription::create('Same')->unwrap();

        self::assertTrue($desc1->equals($desc2));
    }
}
