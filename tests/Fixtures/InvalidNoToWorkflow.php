<?php

namespace Changole\Workflows\Tests\Fixtures;

use Changole\Workflows\Core\TransitionDefinition;
use Changole\Workflows\Core\WorkflowDefinition;

class InvalidNoToWorkflow extends WorkflowDefinition
{
    public function model(): string
    {
        return TestPost::class;
    }

    public function initialState(): string
    {
        return 'draft';
    }

    /**
     * @return array<TransitionDefinition>
     */
    public function transitions(): array
    {
        return [
            $this->transition('submit')->from('draft'),
        ];
    }
}
