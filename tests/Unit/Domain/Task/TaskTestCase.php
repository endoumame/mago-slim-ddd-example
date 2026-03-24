<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Task;

use App\Domain\Task\TaskDescription;
use App\Domain\Task\TaskTitle;
use App\Domain\Task\TodoTask;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
abstract class TaskTestCase extends TestCase
{
    /**
     * @throws \Throwable
     */
    protected function createTestTask(): TodoTask
    {
        $title = TaskTitle::create('Test task')->unwrap();
        $description = TaskDescription::empty();

        return TodoTask::create($title, $description)->unwrap();
    }
}
