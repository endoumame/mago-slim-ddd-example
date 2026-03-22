<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Http;

use DI\Bridge\Slim\Bridge;
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

        $this->app = Bridge::create($container);

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

        $body = $this->parseJson($response);
        self::assertArrayHasKey('data', $body);
        /** @var array<string, mixed> $data */
        $data = $body['data'];
        self::assertSame('Integration test task', $data['title']);
        self::assertSame('Created via test', $data['description']);
        self::assertSame('todo', $data['status']);
    }

    /**
     * @throws \Throwable
     */
    public function testCreateTaskValidationError(): void
    {
        $response = $this->postJson('/api/tasks', ['title' => '']);

        self::assertSame(422, $response->getStatusCode());

        $body = $this->parseJson($response);
        self::assertArrayHasKey('error', $body);
        /** @var array<string, mixed> $error */
        $error = $body['error'];
        self::assertSame('validation_error', $error['type']);
    }

    /**
     * @throws \Throwable
     */
    public function testGetTask(): void
    {
        $createResponse = $this->postJson('/api/tasks', ['title' => 'Find me']);
        $created = $this->parseJson($createResponse);
        self::assertArrayHasKey('data', $created);
        /** @var array<string, mixed> $createdData */
        $createdData = $created['data'];
        /** @var string $id */
        $id = $createdData['id'];

        $response = $this->request('GET', "/api/tasks/{$id}");

        self::assertSame(200, $response->getStatusCode());

        $body = $this->parseJson($response);
        self::assertArrayHasKey('data', $body);
        /** @var array<string, mixed> $data */
        $data = $body['data'];
        self::assertSame('Find me', $data['title']);
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

        $body = $this->parseJson($response);
        self::assertArrayHasKey('data', $body);
        /** @var array<int, mixed> $data */
        $data = $body['data'];
        self::assertCount(2, $data);
    }

    /**
     * @throws \Throwable
     */
    public function testUpdateTask(): void
    {
        $createResponse = $this->postJson('/api/tasks', ['title' => 'Original']);
        $created = $this->parseJson($createResponse);
        self::assertArrayHasKey('data', $created);
        /** @var array<string, mixed> $createdData */
        $createdData = $created['data'];
        /** @var string $id */
        $id = $createdData['id'];

        $response = $this->putJson("/api/tasks/{$id}", ['title' => 'Updated']);

        self::assertSame(200, $response->getStatusCode());
        $body = $this->parseJson($response);
        self::assertArrayHasKey('data', $body);
        /** @var array<string, mixed> $data */
        $data = $body['data'];
        self::assertSame('Updated', $data['title']);
    }

    /**
     * @throws \Throwable
     */
    public function testDeleteTask(): void
    {
        $createResponse = $this->postJson('/api/tasks', ['title' => 'Delete me']);
        $created = $this->parseJson($createResponse);
        self::assertArrayHasKey('data', $created);
        /** @var array<string, mixed> $createdData */
        $createdData = $created['data'];
        /** @var string $id */
        $id = $createdData['id'];

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
        $created = $this->parseJson($createResponse);
        self::assertArrayHasKey('data', $created);
        /** @var array<string, mixed> $createdData */
        $createdData = $created['data'];
        /** @var string $id */
        $id = $createdData['id'];

        $response = $this->patchJson("/api/tasks/{$id}/status", ['status' => 'in_progress']);

        self::assertSame(200, $response->getStatusCode());
        $body = $this->parseJson($response);
        self::assertArrayHasKey('data', $body);
        /** @var array<string, mixed> $data */
        $data = $body['data'];
        self::assertSame('in_progress', $data['status']);
    }

    /**
     * @throws \Throwable
     */
    public function testChangeStatusInvalidTransition(): void
    {
        $createResponse = $this->postJson('/api/tasks', ['title' => 'Status test']);
        $created = $this->parseJson($createResponse);
        self::assertArrayHasKey('data', $created);
        /** @var array<string, mixed> $createdData */
        $createdData = $created['data'];
        /** @var string $id */
        $id = $createdData['id'];

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

        $request = $requestFactory->createServerRequest($method, $uri)
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
