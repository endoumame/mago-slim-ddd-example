<?php

declare(strict_types=1);

namespace App\Domain\Task;

use App\Domain\Task\Exception\InvalidDueDateException;
use DateTimeImmutable;
use Psl\Result\ResultInterface;

use function App\Shared\Result\fail;
use function App\Shared\Result\succeed;

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
     * @return ResultInterface<DueDate>
     */
    public static function create(string $value): ResultInterface
    {
        $parsed = DateTimeImmutable::createFromFormat('Y-m-d', $value);

        if ($parsed === false || $parsed->format('Y-m-d') !== $value) {
            /** @var ResultInterface<DueDate> */
            return fail(InvalidDueDateException::invalidFormat($value));
        }

        $today = new DateTimeImmutable('today');
        if ($parsed < $today) {
            /** @var ResultInterface<DueDate> */
            return fail(InvalidDueDateException::inThePast($value));
        }

        return succeed(new self($parsed));
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
