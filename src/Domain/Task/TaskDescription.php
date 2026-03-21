<?php

declare(strict_types=1);

namespace App\Domain\Task;

use App\Domain\Task\Exception\InvalidTaskDescriptionException;
use Psl\Result\ResultInterface;
use Psl\Str;

use function App\Shared\Result\fail;
use function App\Shared\Result\succeed;

/**
 * @psalm-immutable
 */
final readonly class TaskDescription
{
    private const int MAX_LENGTH = 1000;

    private function __construct(
        private string $value,
    ) {}

    /**
     * Parse a string into a TaskDescription. Returns Failure for invalid values.
     *
     * @return ResultInterface<TaskDescription>
     */
    public static function create(string $value): ResultInterface
    {
        $trimmed = Str\trim($value);
        $length = Str\length($trimmed);

        if ($length > self::MAX_LENGTH) {
            return fail(InvalidTaskDescriptionException::tooLong($length));
        }

        return succeed(new self($trimmed));
    }

    /**
     * Create an empty description.
     */
    public static function empty(): self
    {
        return new self('');
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
