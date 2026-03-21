<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Http;

use DI\Bridge\Slim\Bridge;
use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\StreamFactory;

/** @mago-ignore too-many-methods */
final class TaskEndpointTest extends TestCase
{
    private App $app;

    protected function setUp(): void
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(__DIR__ . '/../../../../config/container.php');
        $container = $containerBuilder->build();

        $this->app = Bridge::create($container);

        (require __DIR__ . '/../../../../config/middleware.php')($this->app);
        (require __DIR__ . '/../../../../config/routes.php')($this->app);
    }

    public function testCreateTask(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'Integration test task',
            'description' => 'Created via test',
        ]);

        self::assertSame(201, $response->getStatusCode());

        $body = $this->parseJson($response);
        self::assertArrayHasKey('data', $body);
        self::assertSame('Integration test task', $body['data']['title']);
        self::assertSame('Created via test', $body['data']['description']);
        self::assertSame('todo', $body['data']['status']);
    }

    public function testCreateTaskValidationError(): void
    {
        $response = $this->postJson('/api/tasks', ['title' => '']);

        self::assertSame(422, $response->getStatusCode());

        $body = $this->parseJson($response);
        self::assertSame('validation_error', $body['error']['type']);
    }

    public function testGetTask(): void
    {
        $createResponse = $this->postJson('/api/tasks', ['title' => 'Find me']);
        $created = $this->parseJson($createResponse);
        $id = $created['data']['id'];

        $response = $this->request('GET', "/api/tasks/{$id}");

        self::assertSame(200, $response->getStatusCode());

        $body = $this->parseJson($response);
        self::assertSame('Find me', $body['data']['title']);
    }

    public function testGetTaskNotFound(): void
    {
        $response = $this->request('GET', '/api/tasks/00000000-0000-4000-8000-000000000000');

        self::assertSame(404, $response->getStatusCode());
    }

    public function testListTasks(): void
    {
        $this->postJson('/api/tasks', ['title' => 'Task 1']);
        $this->postJson('/api/tasks', ['title' => 'Task 2']);

        $response = $this->request('GET', '/api/tasks');

        self::assertSame(200, $response->getStatusCode());

        $body = $this->parseJson($response);
        self::assertCount(2, $body['data']);
    }

    public function testUpdateTask(): void
    {
        $createResponse = $this->postJson('/api/tasks', ['title' => 'Original']);
        $id = $this->parseJson($createResponse)['data']['id'];

        $response = $this->putJson("/api/tasks/{$id}", ['title' => 'Updated']);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Updated', $this->parseJson($response)['data']['title']);
    }

    public function testDeleteTask(): void
    {
        $createResponse = $this->postJson('/api/tasks', ['title' => 'Delete me']);
        $id = $this->parseJson($createResponse)['data']['id'];

        $response = $this->request('DELETE', "/api/tasks/{$id}");

        self::assertSame(204, $response->getStatusCode());

        $getResponse = $this->request('GET', "/api/tasks/{$id}");
        self::assertSame(404, $getResponse->getStatusCode());
    }

    public function testChangeStatus(): void
    {
        $createResponse = $this->postJson('/api/tasks', ['title' => 'Status test']);
        $id = $this->parseJson($createResponse)['data']['id'];

        $response = $this->patchJson("/api/tasks/{$id}/status", ['status' => 'in_progress']);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('in_progress', $this->parseJson($response)['data']['status']);
    }

    public function testChangeStatusInvalidTransition(): void
    {
        $createResponse = $this->postJson('/api/tasks', ['title' => 'Status test']);
        $id = $this->parseJson($createResponse)['data']['id'];

        $response = $this->patchJson("/api/tasks/{$id}/status", ['status' => 'done']);

        self::assertSame(422, $response->getStatusCode());
    }

    /**
     * @param array<string, mixed> $data
     */
    private function postJson(string $uri, array $data): ResponseInterface
    {
        return $this->jsonRequest('POST', $uri, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function putJson(string $uri, array $data): ResponseInterface
    {
        return $this->jsonRequest('PUT', $uri, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function patchJson(string $uri, array $data): ResponseInterface
    {
        return $this->jsonRequest('PATCH', $uri, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function jsonRequest(string $method, string $uri, array $data): ResponseInterface
    {
        $requestFactory = new RequestFactory();
        $streamFactory = new StreamFactory();

        $body = $streamFactory->createStream(json_encode($data, JSON_THROW_ON_ERROR));

        $request = $requestFactory
            ->createRequest($method, $uri)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($body);

        return $this->app->handle($request);
    }

    private function request(string $method, string $uri): ResponseInterface
    {
        $requestFactory = new RequestFactory();

        $request = $requestFactory->createRequest($method, $uri);

        return $this->app->handle($request);
    }

    /**
     * @return array<string, mixed>
     */
    private function parseJson(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();

        /** @var array<string, mixed> */
        return json_decode($body, associative: true, depth: 512, flags: JSON_THROW_ON_ERROR);
    }
}
