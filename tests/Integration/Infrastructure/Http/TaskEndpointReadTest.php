<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Http;

final class TaskEndpointReadTest extends TaskEndpointTestCase
{
    /**
     * @throws \Throwable
     */
    public function testCreateTask(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'Integration test task',
            'description' => 'Created via test',
        ]);

        static::assertSame(201, $response->getStatusCode());

        /** @var array{data: array{title: string, description: string, status: string}} $body */
        $body = $this->parseJson($response);
        static::assertSame('Integration test task', $body['data']['title']);
        static::assertSame('Created via test', $body['data']['description']);
        static::assertSame('todo', $body['data']['status']);
    }

    /**
     * @throws \Throwable
     */
    public function testCreateTaskValidationError(): void
    {
        $response = $this->postJson('/api/tasks', ['title' => '']);

        static::assertSame(422, $response->getStatusCode());

        /** @var array{error: array{type: string}} $body */
        $body = $this->parseJson($response);
        static::assertSame('validation_error', $body['error']['type']);
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

        static::assertSame(200, $response->getStatusCode());

        /** @var array{data: array{title: string}} $body */
        $body = $this->parseJson($response);
        static::assertSame('Find me', $body['data']['title']);
    }

    /**
     * @throws \Throwable
     */
    public function testGetTaskNotFound(): void
    {
        $response = $this->request('GET', '/api/tasks/00000000-0000-4000-8000-000000000000');

        static::assertSame(404, $response->getStatusCode());
    }

    /**
     * @throws \Throwable
     */
    public function testListTasks(): void
    {
        $this->postJson('/api/tasks', ['title' => 'Task 1']);
        $this->postJson('/api/tasks', ['title' => 'Task 2']);

        $response = $this->request('GET', '/api/tasks');

        static::assertSame(200, $response->getStatusCode());

        /** @var array{data: list<mixed>} $body */
        $body = $this->parseJson($response);
        static::assertCount(2, $body['data']);
    }
}
