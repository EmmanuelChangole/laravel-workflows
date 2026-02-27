<?php

namespace Changole\Workflows\Audit;

use Changole\Workflows\Contracts\Auditor;
use Changole\Workflows\Core\WorkflowContext;
use Changole\Workflows\Models\WorkflowTransitionLog;

class EloquentAuditor implements Auditor
{
    public function record(WorkflowContext $ctx, string $transition, string $from, string $to, array $meta = []): void
    {
        WorkflowTransitionLog::query()->create([
            'workflow' => $ctx->workflowName,
            'transition' => $transition,
            'model_type' => $ctx->modelType(),
            'model_id' => (string) $ctx->modelId(),
            'from_state' => $from,
            'to_state' => $to,
            'actor_type' => $ctx->actorType(),
            'actor_id' => $ctx->actorId() === null ? null : (string) $ctx->actorId(),
            'meta' => $meta,
            'created_at' => now(),
        ]);
    }
}
