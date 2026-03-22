<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Middleware;

use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

final class ErrorHandlerMiddleware implements MiddlewareInterface
{
    /**
     * @throws \JsonException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (\Throwable) {
            return $this->createErrorResponse();
        }
    }

    /**
     * @throws \JsonException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    private function createErrorResponse(): ResponseInterface
    {
        $response = new Response(500);
        $response = $response->withHeader('Content-Type', 'application/json');

        $body = json_encode([
            'error' => [
                'type' => 'internal_error',
                'message' => 'An unexpected error occurred.',
            ],
        ], JSON_THROW_ON_ERROR);

        $response->getBody()->write($body);

        return $response;
    }
}
