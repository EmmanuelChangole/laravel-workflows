<?php

namespace Changole\Workflows\Core;

use Changole\Workflows\Contracts\Auditor;
use Changole\Workflows\Contracts\Guard;
use Changole\Workflows\Contracts\Workflow;
use Changole\Workflows\Events\WorkflowBlocked;
use Changole\Workflows\Events\WorkflowTransitioned;
use Changole\Workflows\Events\WorkflowTransitioning;
use Changole\Workflows\Exceptions\GuardDeniedException;
use Changole\Workflows\Exceptions\InvalidTransitionException;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;

class WorkflowEngine
{
    public function __construct(
        private readonly Workflow $workflow,
        private readonly object $model,
    ) {
        $this->assertValidTransitions();
    }

    public function state(): string
    {
        $field = $this->workflow->stateField();
        $current = $this->model->{$field} ?? null;

        if ($current !== null && $current !== '') {
            return (string) $current;
        }

        if ((bool) config('workflow.auto_set_initial_state', true) && $this->model instanceof Model) {
            $initial = $this->workflow->initialState();
            $this->model->{$field} = $initial;
            $this->model->save();

            return $initial;
        }

        return $this->workflow->initialState();
    }

    public function can(string $transition, ?object $actor = null, array $meta = []): bool
    {
        $currentState = $this->state();
        $definition = $this->findTransition($transition);

        if (! in_array($currentState, $definition->getFrom(), true)) {
            return false;
        }

        $ctx = new WorkflowContext($this->model, $actor, $this->workflow->name(), $meta, $transition);

        foreach ($definition->getGuards() as $guard) {
            if (! $this->evaluateGuard($guard, $ctx)) {
                return false;
            }
        }

        return true;
    }

    public function apply(string $transition, ?object $actor = null, array $meta = []): TransitionResult
    {
        $currentState = $this->state();
        $definition = $this->findTransition($transition);

        if (! in_array($currentState, $definition->getFrom(), true)) {
            throw InvalidTransitionException::forState($this->workflow->name(), $transition, $currentState);
        }

        $ctx = new WorkflowContext($this->model, $actor, $this->workflow->name(), $meta, $transition);

        foreach ($definition->getGuards() as $guard) {
            if (! $this->evaluateGuard($guard, $ctx)) {
                $message = $guard instanceof Guard ? $guard->message() : 'Transition blocked by guard.';

                Event::dispatch(new WorkflowBlocked(
                    context: $ctx,
                    transition: $transition,
                    from: $currentState,
                    to: $definition->getTo(),
                    meta: $meta,
                    reason: $message ?? 'Transition blocked by guard.',
                ));

                throw new GuardDeniedException(
                    transition: $transition,
                    workflow: $this->workflow->name(),
                    message: $message,
                );
            }
        }

        Event::dispatch(new WorkflowTransitioning(
            context: $ctx,
            transition: $transition,
            from: $currentState,
            to: $definition->getTo(),
            meta: $meta,
        ));

        $field = $this->workflow->stateField();
        $this->model->{$field} = $definition->getTo();

        if ($this->model instanceof Model) {
            $this->model->save();
        }

        if ((bool) config('workflow.audit.enabled', true)) {
            app(Auditor::class)->record($ctx, $transition, $currentState, $definition->getTo(), $meta);
        }

        Event::dispatch(new WorkflowTransitioned(
            context: $ctx,
            transition: $transition,
            from: $currentState,
            to: $definition->getTo(),
            meta: $meta,
        ));

        return TransitionResult::success($transition, $currentState, $definition->getTo());
    }

    /**
     * @return array<TransitionDefinition>
     */
    public function availableTransitions(?object $actor = null, array $meta = []): array
    {
        $currentState = $this->state();

        return array_values(array_filter(
            $this->workflow->transitions(),
            function (TransitionDefinition $transition) use ($currentState, $actor, $meta): bool {
                if (! in_array($currentState, $transition->getFrom(), true)) {
                    return false;
                }

                $ctx = new WorkflowContext($this->model, $actor, $this->workflow->name(), $meta, $transition->getName());

                foreach ($transition->getGuards() as $guard) {
                    if (! $this->evaluateGuard($guard, $ctx)) {
                        return false;
                    }
                }

                return true;
            }
        ));
    }

    private function findTransition(string $name): TransitionDefinition
    {
        foreach ($this->workflow->transitions() as $transition) {
            if ($transition->getName() === $name) {
                return $transition;
            }
        }

        throw InvalidTransitionException::forState($this->workflow->name(), $name, $this->state());
    }

    private function evaluateGuard(callable|Guard $guard, WorkflowContext $ctx): bool
    {
        if ($guard instanceof Guard) {
            return $guard->allows($ctx);
        }

        return (bool) $guard($ctx);
    }

    private function assertValidTransitions(): void
    {
        $names = [];

        foreach ($this->workflow->transitions() as $transition) {
            if ($transition->getFrom() === []) {
                throw new InvalidArgumentException(sprintf(
                    'Transition [%s] in workflow [%s] must define at least one from state.',
                    $transition->getName(),
                    $this->workflow->name(),
                ));
            }

            if ($transition->getTo() === '') {
                throw new InvalidArgumentException(sprintf(
                    'Transition [%s] in workflow [%s] must define a to state.',
                    $transition->getName(),
                    $this->workflow->name(),
                ));
            }

            if (in_array($transition->getName(), $names, true)) {
                throw new InvalidArgumentException(sprintf(
                    'Duplicate transition name [%s] in workflow [%s].',
                    $transition->getName(),
                    $this->workflow->name(),
                ));
            }

            $names[] = $transition->getName();
        }
    }
}
