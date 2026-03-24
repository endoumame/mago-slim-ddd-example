<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Http;

final class TaskEndpointWriteTest extends TaskEndpointTestCase
{
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

        static::assertSame(200, $response->getStatusCode());
        /** @var array{data: array{title: string}} $body */
        $body = $this->parseJson($response);
        static::assertSame('Updated', $body['data']['title']);
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

        static::assertSame(204, $response->getStatusCode());

        $getResponse = $this->request('GET', "/api/tasks/{$id}");
        static::assertSame(404, $getResponse->getStatusCode());
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

        static::assertSame(200, $response->getStatusCode());
        /** @var array{data: array{status: string}} $body */
        $body = $this->parseJson($response);
        static::assertSame('in_progress', $body['data']['status']);
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

        static::assertSame(422, $response->getStatusCode());
    }
}
