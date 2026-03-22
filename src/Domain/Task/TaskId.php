<?php

declare(strict_types=1);

namespace App\Domain\Task;

use App\Domain\Task\Exception\InvalidTaskIdException;
use Psl\Result\ResultInterface;
use Ramsey\Uuid\Uuid;

use function App\Shared\Result\fail;
use function App\Shared\Result\succeed;

/**
 * @psalm-immutable
 */
final readonly class TaskId
{
    private function __construct(
        private string $value,
    ) {}

    /**
     * Parse a string into a TaskId. Returns Failure for invalid UUIDs.
     *
     * @return ResultInterface<TaskId>
     */
    public static function create(string $value): ResultInterface
    {
        if (!Uuid::isValid($value)) {
            /** @var ResultInterface<TaskId> */
            return fail(InvalidTaskIdException::invalidFormat($value));
        }

        return succeed(new self($value));
    }

    /**
     * Generate a new TaskId with a random UUID v4.
     */
    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
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
