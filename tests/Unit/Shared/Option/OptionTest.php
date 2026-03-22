<?php

declare(strict_types=1);

namespace App\Tests\Unit\Shared\Option;

use App\Shared\Option\None;
use App\Shared\Option\OptionInterface;
use App\Shared\Option\Some;
use PHPUnit\Framework\TestCase;

use function App\Shared\Option\none;
use function App\Shared\Option\of;
use function App\Shared\Option\some;

final class OptionTest extends TestCase
{
    public function testSomeIsSome(): void
    {
        $option = some(42);

        self::assertTrue($option->isSome());
        self::assertFalse($option->isNone());
    }

    public function testNoneIsNone(): void
    {
        $option = none();

        self::assertTrue($option->isNone());
        self::assertFalse($option->isSome());
    }

    public function testOfWithValueReturnsSome(): void
    {
        $option = of('hello');

        self::assertTrue($option->isSome());
        self::assertSame('hello', $option->unwrap());
    }

    public function testOfWithNullReturnsNone(): void
    {
        $option = of(null);

        self::assertTrue($option->isNone());
    }

    public function testUnwrapReturnsSomeValue(): void
    {
        self::assertSame(42, some(42)->unwrap());
    }

    public function testUnwrapThrowsOnNone(): void
    {
        $this->expectException(\RuntimeException::class);
        none()->unwrap();
    }

    public function testUnwrapOrReturnsSomeValue(): void
    {
        self::assertSame(42, some(42)->unwrapOr(99));
    }

    public function testUnwrapOrReturnsDefaultOnNone(): void
    {
        self::assertSame(99, none()->unwrapOr(99));
    }

    public function testMapTransformsSome(): void
    {
        $result = some(5)->map(static fn(int $v): int => $v * 2);

        self::assertTrue($result->isSome());
        self::assertSame(10, $result->unwrap());
    }

    public function testMapSkipsNone(): void
    {
        /** @var OptionInterface<int> */
        $none = none();
        $result = $none->map(static fn(int $v): int => $v * 2);

        self::assertTrue($result->isNone());
    }

    public function testFlatMapChainsSome(): void
    {
        $result = some(5)->flatMap(static fn(int $v): OptionInterface => some($v * 2));

        self::assertTrue($result->isSome());
        self::assertSame(10, $result->unwrap());
    }

    public function testFlatMapReturnsNoneWhenClosureReturnsNone(): void
    {
        $result = some(5)->flatMap(static fn(int $_): OptionInterface => none());

        self::assertTrue($result->isNone());
    }

    public function testFlatMapSkipsNone(): void
    {
        /** @var OptionInterface<int> */
        $none = none();
        $result = $none->flatMap(static fn(int $v): OptionInterface => some($v * 2));

        self::assertTrue($result->isNone());
    }

    public function testFilterKeepsSomeWhenPredicateTrue(): void
    {
        $result = some(10)->filter(static fn(int $v): bool => $v > 5);

        self::assertTrue($result->isSome());
        self::assertSame(10, $result->unwrap());
    }

    public function testFilterDropsSomeWhenPredicateFalse(): void
    {
        $result = some(3)->filter(static fn(int $v): bool => $v > 5);

        self::assertTrue($result->isNone());
    }

    public function testFilterSkipsNone(): void
    {
        /** @var OptionInterface<int> */
        $none = none();
        $result = $none->filter(static fn(int $v): bool => $v > 5);

        self::assertTrue($result->isNone());
    }

    public function testMatchCallsSomeOnSome(): void
    {
        $result = some(42)->match(
            some: static fn(int $v): string => "value: {$v}",
            none: static fn(): string => 'empty',
        );

        self::assertSame('value: 42', $result);
    }

    public function testMatchCallsNoneOnNone(): void
    {
        /** @var OptionInterface<int> */
        $none = none();
        $result = $none->match(
            some: static fn(int $v): string => "value: {$v}",
            none: static fn(): string => 'empty',
        );

        self::assertSame('empty', $result);
    }

    public function testSomeInstanceOf(): void
    {
        $option = some(1);
        self::assertInstanceOf(Some::class, $option);
        self::assertInstanceOf(OptionInterface::class, $option);
    }

    public function testNoneInstanceOf(): void
    {
        $option = none();
        self::assertInstanceOf(None::class, $option);
        self::assertInstanceOf(OptionInterface::class, $option);
    }
}
