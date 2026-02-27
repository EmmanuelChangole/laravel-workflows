<?php

namespace Changole\Workflows\Tests\Fixtures;

use Changole\Workflows\Core\TransitionDefinition;
use Changole\Workflows\Core\WorkflowDefinition;

class TestTaskWorkflow extends WorkflowDefinition
{
    public function model(): string
    {
        return TestTask::class;
    }

    public function initialState(): string
    {
        return 'todo';
    }

    /**
     * @return array<TransitionDefinition>
     */
    public function transitions(): array
    {
        return [
            $this->transition('start')->from('todo')->to('doing'),
        ];
    }
}
