<?php

declare(strict_types=1);

namespace App\Tests\Unit\Shared\Option;

use PHPUnit\Framework\TestCase;
use Psl\Option\Option;
use Psl\Result\ResultInterface;

use function App\Shared\Option\apply_if_some;
use function App\Shared\Option\ok_or;
use function App\Shared\Option\traverse;
use function App\Shared\Result\succeed;
use function Psl\Option\none;
use function Psl\Option\some;

final class OptionFunctionsTest extends TestCase
{
    public function testOkOrConvertsNoneToFailure(): void
    {
        $result = ok_or(none(), new \InvalidArgumentException('missing'));

        self::assertTrue($result->isFailed());
        self::assertInstanceOf(\InvalidArgumentException::class, $result->getThrowable());
    }

    public function testOkOrConvertsSomeToSuccess(): void
    {
        $result = ok_or(some(42), new \InvalidArgumentException('missing'));

        self::assertTrue($result->isSucceeded());
        self::assertSame(42, $result->getResult());
    }

    public function testTraverseWithSomeAppliesFn(): void
    {
        $result = traverse(some('hello'), static fn(string $s): ResultInterface => succeed(mb_strtoupper($s)));

        self::assertTrue($result->isSucceeded());
        self::assertSame('HELLO', $result->getResult());
    }

    public function testTraverseWithNoneReturnsSucceedNull(): void
    {
        /** @var Option<string> */
        $none = none();
        $result = traverse($none, static fn(string $s): ResultInterface => succeed(mb_strtoupper($s)));

        self::assertTrue($result->isSucceeded());
        self::assertNull($result->getResult());
    }

    public function testApplyIfSomeWithSomeAppliesBindFunction(): void
    {
        $initial = succeed(10);

        $fn = apply_if_some(
            some(5),
            static fn(int $extra): \Closure => static fn(int $current): ResultInterface => succeed($current + $extra),
        );

        $result = $fn($initial);

        self::assertTrue($result->isSucceeded());
        self::assertSame(15, $result->getResult());
    }

    public function testApplyIfSomeWithNonePassesThrough(): void
    {
        $initial = succeed(10);

        /** @var Option<int> */
        $none = none();
        $fn = apply_if_some(
            $none,
            static fn(int $extra): \Closure => static fn(int $current): ResultInterface => succeed($current + $extra),
        );

        $result = $fn($initial);

        self::assertTrue($result->isSucceeded());
        self::assertSame(10, $result->getResult());
    }

    public function testApplyIfSomeChaining(): void
    {
        $initial = succeed(0);

        $step1 = apply_if_some(
            some(1),
            static fn(int $v): \Closure => static fn(int $acc): ResultInterface => succeed($acc + $v),
        );

        $step2 = apply_if_some(
            none(),
            static fn(int $v): \Closure => static fn(int $acc): ResultInterface => succeed($acc + $v),
        );

        $step3 = apply_if_some(
            some(3),
            static fn(int $v): \Closure => static fn(int $acc): ResultInterface => succeed($acc + $v),
        );

        $result = $step3($step2($step1($initial)));

        self::assertTrue($result->isSucceeded());
        self::assertSame(4, $result->getResult());
    }
}
