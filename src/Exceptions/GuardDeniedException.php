<?php

namespace Changole\Workflows\Exceptions;

use RuntimeException;

class GuardDeniedException extends RuntimeException
{
    public function __construct(
        public readonly string $transition,
        public readonly string $workflow,
        ?string $message = null,
    ) {
        parent::__construct(
            $message ?? sprintf('Guard denied transition [%s] in workflow [%s].', $transition, $workflow)
        );
    }
}
