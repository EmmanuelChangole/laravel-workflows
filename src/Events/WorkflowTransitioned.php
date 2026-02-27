<?php

namespace Changole\Workflows\Events;

use Changole\Workflows\Core\WorkflowContext;

class WorkflowTransitioned
{
    public function __construct(
        public WorkflowContext $context,
        public string $transition,
        public string $from,
        public string $to,
        public array $meta = [],
    ) {
    }
}
