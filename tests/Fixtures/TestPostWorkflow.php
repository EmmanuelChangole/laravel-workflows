<?php

namespace Changole\Workflows\Tests\Fixtures;

use Changole\Workflows\Core\TransitionDefinition;
use Changole\Workflows\Core\WorkflowContext;
use Changole\Workflows\Core\WorkflowDefinition;

class TestPostWorkflow extends WorkflowDefinition
{
    public function name(): string
    {
        return 'test_post';
    }

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
            $this->transition('submit')->from('draft')->to('pending'),
            $this->transition('approve')
                ->from('pending')
                ->to('approved')
                ->guard(
                    fn (WorkflowContext $ctx): bool => (bool) ($ctx->meta['can_approve'] ?? false),
                    'Approval is not allowed.'
                ),
            $this->transition('reject')->from('pending')->to('rejected'),
        ];
    }
}
