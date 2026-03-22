<?php

declare(strict_types=1);

namespace App\Tests\Unit\Shared\Option;

use PHPUnit\Framework\TestCase;
use Psl\Option\Exception\NoneException;
use Psl\Option\Option;

use function Psl\Option\from_nullable;
use function Psl\Option\none;
use function Psl\Option\some;

final class OptionTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function testSomeIsSome(): void
    {
        $option = some(42);

        self::assertTrue($option->isSome());
        self::assertFalse($option->isNone());
    }

    /**
     * @throws \Throwable
     */
    public function testNoneIsNone(): void
    {
        $option = none();

        self::assertTrue($option->isNone());
        self::assertFalse($option->isSome());
    }

    /**
     * @throws \Throwable
     */
    public function testFromNullableWithValueReturnsSome(): void
    {
        $option = from_nullable('hello');

        self::assertTrue($option->isSome());
        self::assertSame('hello', $option->unwrap());
    }

    /**
     * @throws \Throwable
     */
    public function testFromNullableWithNullReturnsNone(): void
    {
        $option = from_nullable(null);

        self::assertTrue($option->isNone());
    }

    /**
     * @throws \Throwable
     */
    public function testUnwrapReturnsSomeValue(): void
    {
        self::assertSame(42, some(42)->unwrap());
    }

    /**
     * @throws \Throwable
     */
    public function testUnwrapThrowsOnNone(): void
    {
        $this->expectException(NoneException::class);
        none()->unwrap();
    }

    /**
     * @throws \Throwable
     */
    public function testUnwrapOrReturnsSomeValue(): void
    {
        self::assertSame(42, some(42)->unwrapOr(99));
    }

    /**
     * @throws \Throwable
     */
    public function testUnwrapOrReturnsDefaultOnNone(): void
    {
        self::assertSame(99, none()->unwrapOr(99));
    }

    /**
     * @throws \Throwable
     */
    public function testMapTransformsSome(): void
    {
        $result = some(5)->map(static fn(int $v): int => $v * 2);

        self::assertTrue($result->isSome());
        self::assertSame(10, $result->unwrap());
    }

    /**
     * @throws \Throwable
     */
    public function testMapSkipsNone(): void
    {
        /** @var Option<int> */
        $none = none();
        $result = $none->map(static fn(int $v): int => $v * 2);

        self::assertTrue($result->isNone());
    }

    /**
     * @throws \Throwable
     */
    public function testAndThenChainsSome(): void
    {
        $result = some(5)->andThen(static fn(int $v): Option => some($v * 2));

        self::assertTrue($result->isSome());
        self::assertSame(10, $result->unwrap());
    }

    /**
     * @throws \Throwable
     */
    public function testAndThenReturnsNoneWhenClosureReturnsNone(): void
    {
        $result = some(5)->andThen(static fn(int $_): Option => none());

        self::assertTrue($result->isNone());
    }

    /**
     * @throws \Throwable
     */
    public function testAndThenSkipsNone(): void
    {
        /** @var Option<int> */
        $none = none();
        $result = $none->andThen(static fn(int $v): Option => some($v * 2));

        self::assertTrue($result->isNone());
    }

    /**
     * @throws \Throwable
     */
    public function testFilterKeepsSomeWhenPredicateTrue(): void
    {
        $result = some(10)->filter(static fn(int $v): bool => $v > 5);

        self::assertTrue($result->isSome());
        self::assertSame(10, $result->unwrap());
    }

    /**
     * @throws \Throwable
     */
    public function testFilterDropsSomeWhenPredicateFalse(): void
    {
        $result = some(3)->filter(static fn(int $v): bool => $v > 5);

        self::assertTrue($result->isNone());
    }

    /**
     * @throws \Throwable
     */
    public function testFilterSkipsNone(): void
    {
        /** @var Option<int> */
        $none = none();
        $result = $none->filter(static fn(int $v): bool => $v > 5);

        self::assertTrue($result->isNone());
    }

    /**
     * @throws \Throwable
     */
    public function testProceedCallsSomeOnSome(): void
    {
        $result = some(42)->proceed(static fn(int $v): string => "value: {$v}", static fn(): string => 'empty');

        self::assertSame('value: 42', $result);
    }

    /**
     * @throws \Throwable
     */
    public function testProceedCallsNoneOnNone(): void
    {
        /** @var Option<int> */
        $none = none();
        $result = $none->proceed(static fn(int $v): string => "value: {$v}", static fn(): string => 'empty');

        self::assertSame('empty', $result);
    }

    /**
     * @throws \Throwable
     */
    public function testSomeInstanceOf(): void
    {
        $option = some(1);
        self::assertInstanceOf(Option::class, $option);
    }

    /**
     * @throws \Throwable
     */
    public function testNoneInstanceOf(): void
    {
        $option = none();
        self::assertInstanceOf(Option::class, $option);
    }
}
