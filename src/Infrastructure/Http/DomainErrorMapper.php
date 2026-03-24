<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Domain\Task\Exception\DomainError;
use App\Domain\Task\Exception\TaskNotFoundException;

/**
 * @internal
 */
final readonly class DomainErrorMapper
{
    /**
     * @return array{int, string}
     */
    public static function map(\Throwable $error): array
    {
        return match (true) {
            $error instanceof TaskNotFoundException => [404, 'not_found'],
            $error instanceof DomainError, $error instanceof \InvalidArgumentException => [422, 'validation_error'],
            default => [500, 'internal_error'],
        };
    }
}
