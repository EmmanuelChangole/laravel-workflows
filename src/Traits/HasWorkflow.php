<?php

namespace Changole\Workflows\Traits;

use Changole\Workflows\Core\WorkflowEngine;
use Changole\Workflows\Exceptions\WorkflowNotFoundException;

trait HasWorkflow
{
    public function workflow(?string $workflowClass = null): WorkflowEngine
    {
        $workflowClass = $workflowClass ?? ($this->workflow ?? null);

        if (! is_string($workflowClass) || $workflowClass === '') {
            throw new WorkflowNotFoundException('No workflow class was configured for model ['.static::class.'].');
        }

        return new WorkflowEngine(app($workflowClass), $this);
    }
}
