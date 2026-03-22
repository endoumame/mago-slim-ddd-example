<?php

declare(strict_types=1);

namespace App\Shared\Result;

use Closure;
use Psl\Result\Failure;
use Psl\Result\ResultInterface;
use Psl\Result\Success;
use Throwable;

/**
 * Railway-oriented programming utilities built on top of Psl\Result.
 *
 * These functions enable chaining operations that return ResultInterface,
 * forming a "railway" where failures short-circuit through the pipeline.
 */

/**
 * Bind/andThen: Chain an operation that returns a ResultInterface.
 *
 * If the input is a Success, applies $fn to the value and returns its ResultInterface.
 * If the input is a Failure, passes through unchanged.
 *
 * @template T
 * @template U
 * @template Te of Throwable
 *
 * @param ResultInterface<T> $result
 * @param (Closure(T): ResultInterface<U>) $fn
 *
 * @return ResultInterface<U>
 */
function flat_map(ResultInterface $result, Closure $fn): ResultInterface
{
    if ($result->isFailed()) {
        /** @var ResultInterface<U> */
        return $result;
    }

    return $fn($result->getResult());
}

/**
 * Curried form of flat_map for use with the pipeline operator (|>).
 *
 * Returns a closure that accepts a ResultInterface and applies flat_map with the given $fn.
 *
 * Usage: $result |> bind(fn($value) => someOperation($value))
 *
 * @template T
 * @template U
 *
 * @param (Closure(T): ResultInterface<U>) $fn
 *
 * @return (Closure(ResultInterface<T>): ResultInterface<U>)
 */
function bind(Closure $fn): Closure
{
    return static fn(ResultInterface $result): ResultInterface => flat_map($result, $fn);
}

/**
 * Compose multiple functions into a railway pipeline.
 *
 * Each function receives the unwrapped value from the previous Success
 * and must return a ResultInterface. Failures short-circuit the pipeline.
 *
 * @template T
 *
 * @param ResultInterface<T> $initial
 * @param (Closure(mixed): ResultInterface<mixed>) ...$fns
 *
 * @return ResultInterface<mixed>
 */
function pipeline(ResultInterface $initial, Closure ...$fns): ResultInterface
{
    $result = $initial;

    foreach ($fns as $fn) {
        $result = flat_map($result, $fn);
    }

    return $result;
}

/**
 * Succeed with a value.
 *
 * @template T
 *
 * @param T $value
 *
 * @return ResultInterface<T>
 */
function succeed(mixed $value): ResultInterface
{
    return new Success($value);
}

/**
 * Fail with a Throwable.
 *
 * @template T
 *
 * @param Throwable $error
 *
 * @return ResultInterface<T>
 */
function fail(Throwable $error): ResultInterface
{
    /** @var ResultInterface<T> */
    return new Failure($error);
}
