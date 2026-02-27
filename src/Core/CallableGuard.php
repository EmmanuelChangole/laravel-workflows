<?php

namespace Changole\Workflows\Core;

use Changole\Workflows\Contracts\Guard;

final class CallableGuard implements Guard
{
    public function __construct(
        private readonly mixed $callback,
        private readonly ?string $guardMessage = null,
    ) {
    }

    public function allows(WorkflowContext $ctx): bool
    {
        return (bool) call_user_func($this->callback, $ctx);
    }

    public function message(): ?string
    {
        return $this->guardMessage;
    }
}
