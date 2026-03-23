<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use Closure;
use Psr\Container\ContainerInterface;
use Slim\Interfaces\AdvancedCallableResolverInterface;

/**
 * A CallableResolver that does NOT rebind Closure $this to the container.
 *
 * Slim's default CallableResolver calls Closure::bindTo($container) on every
 * closure, which silently replaces $this. This breaks first-class callables
 * like $controller->method(...) where $this must remain the controller.
 *
 * This resolver delegates to the container for string/array resolution but
 * leaves closures untouched.
 *
 * @internal
 */
final readonly class CallableResolver implements AdvancedCallableResolverInterface
{
    public function __construct(
        private ContainerInterface $container,
    ) {}

    /**
     * @throws \RuntimeException
     */
    #[\Override]
    public function resolve($toResolve): callable
    {
        return $this->doResolve($toResolve);
    }

    /**
     * @throws \RuntimeException
     */
    #[\Override]
    public function resolveRoute($toResolve): callable
    {
        return $this->doResolve($toResolve);
    }

    /**
     * @throws \RuntimeException
     */
    #[\Override]
    public function resolveMiddleware($toResolve): callable
    {
        return $this->doResolve($toResolve);
    }

    /**
     * @throws \RuntimeException
     */
    private function doResolve(mixed $toResolve): callable
    {
        if ($toResolve instanceof Closure) {
            return $toResolve;
        }

        try {
            if (
                is_array($toResolve)
                && array_is_list($toResolve)
                && count($toResolve) === 2
                && is_string($toResolve[0])
                && is_string($toResolve[1])
            ) {
                return $this->buildClosure($this->resolveClass($toResolve[0]), $toResolve[1]);
            }

            if (is_string($toResolve) && !is_callable($toResolve)) {
                return $this->buildClosure($this->resolveClass($toResolve), '__invoke');
            }
        } catch (\ReflectionException|\Psr\Container\ContainerExceptionInterface $e) {
            throw new \RuntimeException($e->getMessage(), 0, $e);
        }

        if (is_callable($toResolve)) {
            return $toResolve;
        }

        throw new \RuntimeException(sprintf('Unable to resolve callable: %s', (string) json_encode($toResolve)));
    }

    /**
     * @throws \ReflectionException
     */
    private function buildClosure(object $instance, string $method): Closure
    {
        $ref = new \ReflectionMethod($instance, $method);

        return static fn(mixed ...$args): mixed => $ref->invokeArgs($instance, $args);
    }

    /**
     * @throws \RuntimeException
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    private function resolveClass(string $class): object
    {
        if (!$this->container->has($class)) {
            throw new \RuntimeException(sprintf('Callable class %s does not exist in container', $class));
        }

        if (!is_object($this->container->get($class))) {
            throw new \RuntimeException(sprintf('Container entry %s is not an object', $class));
        }

        /** @var object — container caches resolved instances; type verified by is_object above */
        return $this->container->get($class);
    }
}
