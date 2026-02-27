<?php

namespace Changole\Workflows\Contracts;

use Changole\Workflows\Core\WorkflowContext;

interface Auditor
{
    public function record(WorkflowContext $ctx, string $transition, string $from, string $to, array $meta = []): void;
}
