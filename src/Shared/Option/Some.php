<?php

declare(strict_types=1);

namespace App\Shared\Option;

use Closure;

/**
 * Contains a value.
 *
 * @template T
 *
 * @implements OptionInterface<T>
 */
final readonly class Some implements OptionInterface
{
    /**
     * @param T $value
     */
    public function __construct(
        private mixed $value,
    ) {}

    public function isSome(): bool
    {
        return true;
    }

    public function isNone(): bool
    {
        return false;
    }

    public function unwrap(): mixed
    {
        return $this->value;
    }

    public function unwrapOr(mixed $default): mixed
    {
        return $this->value;
    }

    public function map(Closure $fn): OptionInterface
    {
        return new self($fn($this->value));
    }

    public function flatMap(Closure $fn): OptionInterface
    {
        return $fn($this->value);
    }

    public function filter(Closure $predicate): OptionInterface
    {
        if ($predicate($this->value)) {
            return $this;
        }

        return new None();
    }

    public function match(Closure $some, Closure $none): mixed
    {
        return $some($this->value);
    }
}
