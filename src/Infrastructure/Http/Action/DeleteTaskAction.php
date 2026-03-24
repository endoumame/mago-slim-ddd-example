<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action;

use App\Application\Task\Command\DeleteTaskCommand;
use App\Application\Task\Handler\DeleteTaskHandler;
use App\Infrastructure\Http\JsonResponseFactory;
use Psr\Http\Message\ResponseInterface;

final readonly class DeleteTaskAction
{
    public function __construct(
        private DeleteTaskHandler $handler,
    ) {}

    /**
     * @throws \Throwable
     */
    public function __invoke(string $id): ResponseInterface
    {
        return JsonResponseFactory::fromDeleteResult($this->handler->handle(new DeleteTaskCommand(id: $id)));
    }
}
