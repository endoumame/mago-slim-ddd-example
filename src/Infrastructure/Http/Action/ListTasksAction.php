<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action;

use App\Application\Task\List\ListTasks;
use App\Application\Task\List\ListTasksHandler;
use App\Infrastructure\Http\JsonResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ListTasksAction
{
    public function __construct(
        private ListTasksHandler $handler,
    ) {}

    /**
     * @throws \Throwable
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        $query = new ListTasks(status: \array_key_exists('status', $params) ? (string) $params['status'] : null);

        return JsonResponseFactory::fromTaskListResult($this->handler->handle($query));
    }
}
