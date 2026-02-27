<?php

namespace Changole\Workflows\Core;

use Changole\Workflows\Contracts\Workflow;
use Illuminate\Support\Str;

abstract class WorkflowDefinition implements Workflow
{
    public function name(): string
    {
        return Str::snake(class_basename(static::class));
    }

    public function stateField(): string
    {
        return (string) config('workflow.state_field', 'state');
    }

    protected function transition(string $name): TransitionDefinition
    {
        return TransitionDefinition::make($name);
    }

    abstract public function model(): string;

    abstract public function initialState(): string;

    /**
     * @return array<TransitionDefinition>
     */
    abstract public function transitions(): array;
}
