<?php

namespace Changole\Workflows\Core;

use Illuminate\Database\Eloquent\Model;

final class WorkflowContext
{
    public function __construct(
        public readonly object $model,
        public readonly ?object $actor,
        public readonly string $workflowName,
        public readonly array $meta = [],
        public readonly ?string $transition = null,
    ) {
    }

    public function withTransition(string $transition): self
    {
        return new self(
            model: $this->model,
            actor: $this->actor,
            workflowName: $this->workflowName,
            meta: $this->meta,
            transition: $transition,
        );
    }

    public function modelType(): string
    {
        return $this->model::class;
    }

    public function modelId(): string|int
    {
        if ($this->model instanceof Model) {
            return $this->model->getKey();
        }

        return spl_object_id($this->model);
    }

    public function actorType(): ?string
    {
        return $this->actor ? $this->actor::class : null;
    }

    public function actorId(): string|int|null
    {
        if ($this->actor === null) {
            return null;
        }

        if ($this->actor instanceof Model) {
            return $this->actor->getKey();
        }

        return spl_object_id($this->actor);
    }
}
