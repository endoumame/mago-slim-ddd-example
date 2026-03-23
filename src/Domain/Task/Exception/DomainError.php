<?php

declare(strict_types=1);

namespace App\Domain\Task\Exception;

use DomainException;

/**
 * Base class for domain errors.
 *
 * These are never thrown — they are always wrapped in EndouMame\PhpMonad\Result\Err
 * and returned as values for railway-oriented programming.
 *
 * @api
 */
abstract class DomainError extends DomainException {}
