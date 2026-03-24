<?php

declare(strict_types=1);

namespace App\Shared\Result;

use Closure;
use EndouMame\PhpMonad\Result;

use function EndouMame\PhpMonad\Result\ok;

/**
 * Apply a pure function to the unwrapped values of multiple Results.
 *
 * If all Results are Ok, applies $fn to the unwrapped values and wraps the return in Ok.
 * Returns the first Err encountered if any Result is Err (short-circuit).
 *
 * @template T
 * @template E
 *
 * @param (Closure(mixed...): T) $fn
 * @param Result<mixed, E> ...$results
 *
 * @return Result<T, E>
 *
 * @throws \Throwable
 */
function map_all(Closure $fn, Result ...$results): Result
{
    $values = [];
    foreach ($results as $result) {
        if ($result->isErr()) {
            return $result;
        }
        $values[] = $result->unwrap();
    }

    return ok($fn(...$values));
}

/**
 * Apply a Result-returning function to the unwrapped values of multiple Results.
 *
 * If all Results are Ok, applies $fn to the unwrapped values.
 * Returns the first Err encountered if any Result is Err (short-circuit).
 *
 * This is the flatMap/bind variant of map_all — use when $fn itself returns a Result.
 *
 * @template T
 * @template E
 *
 * @param (Closure(mixed...): Result<T, E>) $fn
 * @param Result<mixed, E> ...$results
 *
 * @return Result<T, E>
 *
 * @throws \Throwable
 */
function and_then_all(Closure $fn, Result ...$results): Result
{
    $values = [];
    foreach ($results as $result) {
        if ($result->isErr()) {
            return $result;
        }
        $values[] = $result->unwrap();
    }

    return $fn(...$values);
}
