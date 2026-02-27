<?php

namespace Changole\Workflows\Exceptions;

use RuntimeException;

class InvalidTransitionException extends RuntimeException
{
    public static function forState(string $workflowName, string $transition, string $currentState): self
    {
        return new self(sprintf(
            'Invalid transition [%s] for workflow [%s] from current state [%s].',
            $transition,
            $workflowName,
            $currentState,
        ));
    }
}
