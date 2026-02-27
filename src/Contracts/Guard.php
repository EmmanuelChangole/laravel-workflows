<?php

namespace Changole\Workflows\Contracts;

use Changole\Workflows\Core\WorkflowContext;

interface Guard
{
    public function allows(WorkflowContext $ctx): bool;

    public function message(): ?string;
}
