<?php

namespace Tests\Platform\Domains\TaskManager;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\TaskManager\Contracts\Task;
use SuperV\Platform\Testing\PlatformTestCase;
use Tests\Platform\Domains\TaskManager\Fixtures\TestHandler;

abstract class TaskManagerTest extends PlatformTestCase
{
    use RefreshDatabase;

    protected function makeTaskModel(array $taskData = null): Task
    {
        return sv_resource('sv.platform.tasks')->create($taskData ?? $this->makeTaskData());
    }

    protected function makeTaskData(array $overrides = []): array
    {
        return array_merge(
            ['title'   => 'Run Recipe',
             //             'status'  => 'pending',
             'handler' => TestHandler::class,
             'payload' => $this->makeTaskPayload()], $overrides
        );
    }

    protected function makeTaskPayload(): array
    {
        return ['server_id' => 3,
                'recipe_id' => 5,
        ];
    }
}
