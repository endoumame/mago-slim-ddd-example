<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Http;

use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;

final class TaskEndpointTest extends TestCase
{
    private App $app;

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
     * @throws \Throwable
     */
    public function testCreateTask(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'Integration test task',
            'description' => 'Created via test',
        ]);

        self::assertSame(201, $response->getStatusCode());

        /** @var array{data: array{title: string, description: string, status: string}} $body */
        $body = $this->parseJson($response);
        self::assertSame('Integration test task', $body['data']['title']);
        self::assertSame('Created via test', $body['data']['description']);
        self::assertSame('todo', $body['data']['status']);
    }

    /**
     * @throws \Throwable
     */
    public function testCreateTaskValidationError(): void
    {
        $response = $this->postJson('/api/tasks', ['title' => '']);

        self::assertSame(422, $response->getStatusCode());

        /** @var array{error: array{type: string}} $body */
        $body = $this->parseJson($response);
        self::assertSame('validation_error', $body['error']['type']);
    }

    /**
     * @throws \Throwable
     */
    public function testGetTask(): void
    {
        $createResponse = $this->postJson('/api/tasks', ['title' => 'Find me']);
        /** @var array{data: array{id: string}} $created */
        $created = $this->parseJson($createResponse);
        $id = $created['data']['id'];

        $response = $this->request('GET', "/api/tasks/{$id}");

        self::assertSame(200, $response->getStatusCode());

        /** @var array{data: array{title: string}} $body */
        $body = $this->parseJson($response);
        self::assertSame('Find me', $body['data']['title']);
    }

    /**
     * @throws \Throwable
     */
    public function testGetTaskNotFound(): void
    {
        $response = $this->request('GET', '/api/tasks/00000000-0000-4000-8000-000000000000');

        self::assertSame(404, $response->getStatusCode());
    }

    /**
     * @throws \Throwable
     */
    public function testListTasks(): void
    {
        $this->postJson('/api/tasks', ['title' => 'Task 1']);
        $this->postJson('/api/tasks', ['title' => 'Task 2']);

        $response = $this->request('GET', '/api/tasks');

        self::assertSame(200, $response->getStatusCode());

        /** @var array{data: list<mixed>} $body */
        $body = $this->parseJson($response);
        self::assertCount(2, $body['data']);
    }

    /**
     * @throws \Throwable
     */
    public function testUpdateTask(): void
    {
        $createResponse = $this->postJson('/api/tasks', ['title' => 'Original']);
        /** @var array{data: array{id: string}} $created */
        $created = $this->parseJson($createResponse);
        $id = $created['data']['id'];

        $response = $this->putJson("/api/tasks/{$id}", ['title' => 'Updated']);

        self::assertSame(200, $response->getStatusCode());
        /** @var array{data: array{title: string}} $body */
        $body = $this->parseJson($response);
        self::assertSame('Updated', $body['data']['title']);
    }

    /**
     * @throws \Throwable
     */
    public function testDeleteTask(): void
    {
        $createResponse = $this->postJson('/api/tasks', ['title' => 'Delete me']);
        /** @var array{data: array{id: string}} $created */
        $created = $this->parseJson($createResponse);
        $id = $created['data']['id'];

        $response = $this->request('DELETE', "/api/tasks/{$id}");

        self::assertSame(204, $response->getStatusCode());

        $getResponse = $this->request('GET', "/api/tasks/{$id}");
        self::assertSame(404, $getResponse->getStatusCode());
    }

    /**
     * @throws \Throwable
     */
    public function testChangeStatus(): void
    {
        $createResponse = $this->postJson('/api/tasks', ['title' => 'Status test']);
        /** @var array{data: array{id: string}} $created */
        $created = $this->parseJson($createResponse);
        $id = $created['data']['id'];

        $response = $this->patchJson("/api/tasks/{$id}/status", ['status' => 'in_progress']);

        self::assertSame(200, $response->getStatusCode());
        /** @var array{data: array{status: string}} $body */
        $body = $this->parseJson($response);
        self::assertSame('in_progress', $body['data']['status']);
    }

    /**
     * @throws \Throwable
     */
    public function testChangeStatusInvalidTransition(): void
    {
        $createResponse = $this->postJson('/api/tasks', ['title' => 'Status test']);
        /** @var array{data: array{id: string}} $created */
        $created = $this->parseJson($createResponse);
        $id = $created['data']['id'];

        $response = $this->patchJson("/api/tasks/{$id}/status", ['status' => 'done']);

        self::assertSame(422, $response->getStatusCode());
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws \Throwable
     */
    private function postJson(string $uri, array $data): ResponseInterface
    {
        return $this->jsonRequest('POST', $uri, $data);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws \Throwable
     */
    private function putJson(string $uri, array $data): ResponseInterface
    {
        return $this->jsonRequest('PUT', $uri, $data);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws \Throwable
     */
    private function patchJson(string $uri, array $data): ResponseInterface
    {
        return $this->jsonRequest('PATCH', $uri, $data);
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

        $body = $streamFactory->createStream(json_encode($data, JSON_THROW_ON_ERROR));

        $request = $requestFactory
            ->createServerRequest($method, $uri)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($body);

        return $this->app->handle($request);
    }

    /**
     * @throws \Throwable
     */
    private function request(string $method, string $uri): ResponseInterface
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
    private function parseJson(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();

        /** @var array<string, mixed> */
        return json_decode($body, associative: true, depth: 512, flags: JSON_THROW_ON_ERROR);
    }
}
