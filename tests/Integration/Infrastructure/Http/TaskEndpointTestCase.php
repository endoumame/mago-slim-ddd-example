<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Http;

use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;

/**
 * @internal
 */
abstract class TaskEndpointTestCase extends TestCase
{
    protected App $app;

    /**
     * @throws \Throwable
     */
    #[\Override]
    protected function setUp(): void
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(__DIR__ . '/../../../../config/container.php');
        $container = $containerBuilder->build();

        $this->app = new App(
            new \Slim\Psr7\Factory\ResponseFactory(),
            $container,
            new \App\Infrastructure\Http\CallableResolver(),
        );

        /** @var callable(App): void $middleware */
        $middleware = require __DIR__ . '/../../../../config/middleware.php';
        $middleware($this->app);

        /** @var callable(App): void $routes */
        $routes = require __DIR__ . '/../../../../config/routes.php';
        $routes($this->app);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws \Throwable
     */
    protected function postJson(string $uri, array $data): ResponseInterface
    {
        return $this->jsonRequest('POST', $uri, $data);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws \Throwable
     */
    protected function putJson(string $uri, array $data): ResponseInterface
    {
        return $this->jsonRequest('PUT', $uri, $data);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws \Throwable
     */
    protected function patchJson(string $uri, array $data): ResponseInterface
    {
        return $this->jsonRequest('PATCH', $uri, $data);
    }

    /**
     * @throws \Throwable
     */
    protected function request(string $method, string $uri): ResponseInterface
    {
        $requestFactory = new ServerRequestFactory();

        $request = $requestFactory->createServerRequest($method, $uri);

        return $this->app->handle($request);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Throwable
     */
    protected function parseJson(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();

        /** @var array<string, mixed> */
        return \json_decode($body, associative: true, depth: 512, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws \Throwable
     */
    private function jsonRequest(string $method, string $uri, array $data): ResponseInterface
    {
        $requestFactory = new ServerRequestFactory();
        $streamFactory = new StreamFactory();

        $body = $streamFactory->createStream(\json_encode($data, JSON_THROW_ON_ERROR));

        $request = $requestFactory
            ->createServerRequest($method, $uri)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($body);

        return $this->app->handle($request);
    }
}
