<?php

declare(strict_types=1);

namespace App\Shared\Option;

use Closure;
use Psl\Result\ResultInterface;
use Throwable;

use function App\Shared\Result\bind;
use function App\Shared\Result\fail;
use function App\Shared\Result\succeed;

/**
 * Wrap a value in Some.
 *
 * @template T
 *
 * @param T $value
 *
 * @return OptionInterface<T>
 */
function some(mixed $value): OptionInterface
{
    return new Some($value);
}

/**
 * Return a None instance.
 *
 * @return OptionInterface<never>
 */
function none(): OptionInterface
{
    return new None();
}

/**
 * Create an Option from a nullable value.
 *
 * @template T
 *
 * @param T|null $value
 *
 * @return OptionInterface<T>
 */
function of(mixed $value): OptionInterface
{
    if ($value === null) {
        return none();
    }

    return some($value);
}

/**
 * Convert an Option to a Result.
 *
 * Some($value) becomes succeed($value).
 * None becomes fail($error).
 *
 * @template T
 *
 * @param OptionInterface<T> $option
 * @param Throwable $error
 *
 * @return ResultInterface<T>
 */
function ok_or(OptionInterface $option, Throwable $error): ResultInterface
{
    return $option->match(
        some: static fn(mixed $value): ResultInterface => succeed($value),
        none: static fn(): ResultInterface => fail($error),
    );
}

/**
 * Apply a function that returns ResultInterface if Some, or succeed(null) if None.
 *
 * Useful for optional value object creation:
 *   traverse(of($command->dueDate), fn(string $d) => DueDate::create($d))
 *
 * @template T
 * @template U
 *
 * @param OptionInterface<T> $option
 * @param (Closure(T): ResultInterface<U>) $fn
 *
 * @return ResultInterface<U|null>
 */
function traverse(OptionInterface $option, Closure $fn): ResultInterface
{
    return $option->match(
        some: static fn(mixed $value): ResultInterface => $fn($value),
        none: static fn(): ResultInterface => succeed(null),
    );
}

/**
 * Conditionally apply a bind operation based on an Option value.
 *
 * If Some, applies $fn(unwrapped value) which must return a Closure suitable for bind().
 * If None, returns an identity function that passes ResultInterface through unchanged.
 *
 * Usage with pipeline operator:
 *   succeed($task)
 *       |> apply_if_some(of($title), fn(string $t) => fn(Task $task) => ...)
 *
 * @template T
 * @template V
 * @template W
 *
 * @param OptionInterface<T> $option
 * @param (Closure(T): (Closure(V): ResultInterface<W>)) $fn
 *
 * @return (Closure(ResultInterface<V>): ResultInterface<V|W>)
 */
function apply_if_some(OptionInterface $option, Closure $fn): Closure
{
    if ($option->isNone()) {
        return static fn(ResultInterface $result): ResultInterface => $result;
    }

    return bind($fn($option->unwrap()));
}
