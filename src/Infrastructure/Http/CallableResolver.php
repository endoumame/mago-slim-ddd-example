<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use Closure;
use Slim\Interfaces\AdvancedCallableResolverInterface;

/**
 * A pass-through CallableResolver that skips Slim's Closure::bindTo() rebinding.
 *
 * Slim's default CallableResolver rebinds every Closure's $this to the DI
 * container, breaking first-class callables like $controller->method(...).
 * This resolver simply returns Closures as-is.
 *
 * @internal
 */
final class CallableResolver implements AdvancedCallableResolverInterface
{
    /**
     * @throws \RuntimeException
     */
    #[\Override]
    public function resolve($toResolve): callable
    {
        if ($toResolve instanceof Closure) {
            return $toResolve;
        }

        throw new \RuntimeException(sprintf('Expected Closure, got %s', get_debug_type($toResolve)));
    }

    /**
     * @throws \RuntimeException
     */
    #[\Override]
    public function resolveRoute($toResolve): callable
    {
        return $this->resolve($toResolve);
    }

    /**
     * @throws \RuntimeException
     */
    #[\Override]
    public function resolveMiddleware($toResolve): callable
    {
        return $this->resolve($toResolve);
    }
}
