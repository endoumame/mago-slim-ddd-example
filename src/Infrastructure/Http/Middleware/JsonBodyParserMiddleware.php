<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Middleware;

use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class JsonBodyParserMiddleware implements MiddlewareInterface
{
    /**
     * @throws \InvalidArgumentException
     */
    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $contentType = $request->getHeaderLine('Content-Type');

        if (str_contains($contentType, 'application/json')) {
            $body = (string) $request->getBody();

            if ($body !== '') {
                /** @var array<string, mixed>|null $decoded */
                $decoded = json_decode($body, associative: true);

                if (is_array($decoded)) {
                    $request = $request->withParsedBody($decoded);
                }
            }
        }

        return $handler->handle($request);
    }
}
