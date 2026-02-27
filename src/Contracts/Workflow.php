<?php

namespace Changole\Workflows\Contracts;

interface Workflow
{
    public function name(): string;

    public function model(): string;

    public function stateField(): string;

    public function initialState(): string;

    /**
     * @return array<\Changole\Workflows\Core\TransitionDefinition>
     */
    public function transitions(): array;
}
