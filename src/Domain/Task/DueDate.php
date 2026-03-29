<?php

declare(strict_types=1);

namespace App\Domain\Task;

use App\Domain\Task\Exception\InvalidDueDateException;
use DateTimeImmutable;
use EndouMame\PhpMonad\Result;

use function EndouMame\PhpMonad\Result\err;
use function EndouMame\PhpMonad\Result\ok;

/**
 * @psalm-immutable
 */
final readonly class DueDate
{
    private function __construct(
        private DateTimeImmutable $value,
    ) {}

    /**
     * Parse a date string (Y-m-d) into a DueDate.
     * The date must be today or in the future.
     *
     * @return Result<DueDate, InvalidDueDateException>
     */
    public static function create(string $value): Result
    {
        $parsed = DateTimeImmutable::createFromFormat('Y-m-d', $value);

        if ($parsed === false || $parsed->format('Y-m-d') !== $value) {
            /** @var Result<DueDate, InvalidDueDateException> */
            return err(InvalidDueDateException::invalidFormat($value));
        }

        $today = new DateTimeImmutable('today');
        if ($parsed < $today) {
            /** @var Result<DueDate, InvalidDueDateException> */
            return err(InvalidDueDateException::inThePast($value));
        }

        return ok(new self($parsed));
    }

    /**
     * Reconstitute a DueDate from persistence. Skips the "not in the past" validation
     * because persisted tasks may already have past due dates.
     */
    public static function reconstitute(string $value): self
    {
        $parsed = DateTimeImmutable::createFromFormat('Y-m-d', $value);
        \assert($parsed !== false, "Invalid date format during reconstitution: {$value}");

        return new self($parsed);
    }

    /**
     * Returns true if this due date is strictly before the reference date (compared at day granularity).
     */
    public function isOverdue(DateTimeImmutable $referenceDate): bool
    {
        return $this->value->format('Y-m-d') < $referenceDate->format('Y-m-d');
    }

    public function value(): DateTimeImmutable
    {
        return $this->value;
    }

    public function format(string $format = 'Y-m-d'): string
    {
        return $this->value->format($format);
    }

    public function equals(self $other): bool
    {
        return $this->value->format('Y-m-d') === $other->value->format('Y-m-d');
    }

    public function __toString(): string
    {
        return $this->format();
    }
}
