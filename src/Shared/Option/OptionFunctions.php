<?php

declare(strict_types=1);

namespace App\Shared\Option;

use Closure;
use Psl\Option\Option;
use Psl\Result\ResultInterface;
use Throwable;

use function App\Shared\Result\bind;
use function App\Shared\Result\fail;
use function App\Shared\Result\succeed;

/**
 * Convert an Option to a Result.
 *
 * Some($value) becomes succeed($value).
 * None becomes fail($error).
 *
 * @template T
 *
 * @param Option<T> $option
 * @param Throwable $error
 *
 * @return ResultInterface<T>
 */
function ok_or(Option $option, Throwable $error): ResultInterface
{
    return $option->proceed(
        /** @param T $value */
        static fn(mixed $value): ResultInterface => succeed($value),
        static fn(): ResultInterface => fail($error),
    );
}

/**
 * Apply a function that returns ResultInterface if Some, or succeed(null) if None.
 *
 * Useful for optional value object creation:
 *   traverse(from_nullable($command->dueDate), fn(string $d) => DueDate::create($d))
 *
 * @template T
 * @template U
 *
 * @param Option<T> $option
 * @param (Closure(T): ResultInterface<U>) $fn
 *
 * @return ResultInterface<U|null>
 */
function traverse(Option $option, Closure $fn): ResultInterface
{
    return $option->proceed(
        /** @param T $value */
        static fn(mixed $value): ResultInterface => $fn($value),
        static fn(): ResultInterface => succeed(null),
    );
}

/**
 * Curried form of ok_or for use with the pipeline operator (|>).
 *
 * Usage: $nullable |> from_nullable(...) |> ok_or_err($error)
 *
 * @template T
 *
 * @return (Closure(Option<T>): ResultInterface<T>)
 */
function ok_or_err(Throwable $error): Closure
{
    return static fn(Option $option): ResultInterface => ok_or($option, $error);
}

/**
 * Curried form of traverse for use with the pipeline operator (|>).
 *
 * Usage: $nullable |> from_nullable(...) |> traverse_with(DueDate::create(...))
 *
 * @template T
 * @template U
 *
 * @param (Closure(T): ResultInterface<U>) $fn
 *
 * @return (Closure(Option<T>): ResultInterface<U|null>)
 */
function traverse_with(Closure $fn): Closure
{
    return static fn(Option $option): ResultInterface => traverse($option, $fn);
}

/**
 * Conditionally apply a bind operation based on an Option value.
 *
 * If Some, applies $fn(unwrapped value) which must return a Closure suitable for bind().
 * If None, returns an identity function that passes ResultInterface through unchanged.
 *
 * Usage with pipeline operator:
 *   succeed($task)
 *       |> apply_if_some(from_nullable($title), fn(string $t) => fn(Task $task) => ...)
 *
 * @template T
 * @template V
 * @template W
 *
 * @param Option<T> $option
 * @param (Closure(T): (Closure(V): ResultInterface<W>)) $fn
 *
 * @return (Closure(ResultInterface<V>): ResultInterface<V|W>)
 */
function apply_if_some(Option $option, Closure $fn): Closure
{
    return $option->proceed(
        static function (mixed $value) use ($fn): Closure {
            /** @var Closure(mixed): ResultInterface<mixed> $binding */
            $binding = $fn($value);

            return bind($binding);
        },
        static fn(): Closure => static fn(ResultInterface $result): ResultInterface => $result,
    );
}
