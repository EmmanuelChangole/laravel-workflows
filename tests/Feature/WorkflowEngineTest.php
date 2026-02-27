<?php

use Changole\Workflows\Events\WorkflowBlocked;
use Changole\Workflows\Events\WorkflowTransitioned;
use Changole\Workflows\Events\WorkflowTransitioning;
use Changole\Workflows\Exceptions\GuardDeniedException;
use Changole\Workflows\Exceptions\InvalidTransitionException;
use Changole\Workflows\Exceptions\WorkflowNotFoundException;
use Changole\Workflows\Models\WorkflowTransitionLog;
use Changole\Workflows\Tests\Fixtures\InvalidDuplicateWorkflow;
use Changole\Workflows\Tests\Fixtures\InvalidNoFromWorkflow;
use Changole\Workflows\Tests\Fixtures\InvalidNoToWorkflow;
use Changole\Workflows\Tests\Fixtures\TestPost;
use Changole\Workflows\Tests\Fixtures\TestPostWithoutWorkflow;
use Changole\Workflows\Tests\Fixtures\TestTask;
use Illuminate\Support\Facades\Event;

it('applies valid transition updates model state and writes audit log', function (): void {
    $post = TestPost::query()->create(['state' => 'draft']);

    $result = $post->workflow()->apply('submit', null, ['source' => 'test']);

    expect($result->ok)->toBeTrue();
    expect($post->fresh()->state)->toBe('pending');

    $log = WorkflowTransitionLog::query()->first();

    expect($log)->not->toBeNull();
    expect($log->workflow)->toBe('test_post');
    expect($log->transition)->toBe('submit');
    expect($log->from_state)->toBe('draft');
    expect($log->to_state)->toBe('pending');
});

it('cannot apply transition from wrong state', function (): void {
    $post = TestPost::query()->create(['state' => 'draft']);

    $post->workflow()->apply('approve', null, ['can_approve' => true]);
})->throws(InvalidTransitionException::class);

it('throws guard denied and does not change state or write audit and fires blocked event', function (): void {
    Event::fake([WorkflowBlocked::class]);

    $post = TestPost::query()->create(['state' => 'pending']);

    expect(fn () => $post->workflow()->apply('approve'))->toThrow(GuardDeniedException::class);

    expect($post->fresh()->state)->toBe('pending');
    expect(WorkflowTransitionLog::query()->count())->toBe(0);

    Event::assertDispatched(WorkflowBlocked::class, function (WorkflowBlocked $event): bool {
        return $event->transition === 'approve'
            && $event->from === 'pending'
            && $event->to === 'approved'
            && $event->reason === 'Approval is not allowed.';
    });
});

it('dispatches transitioning and transitioned on success', function (): void {
    Event::fake([WorkflowTransitioning::class, WorkflowTransitioned::class]);

    $post = TestPost::query()->create(['state' => 'draft']);

    $post->workflow()->apply('submit');

    Event::assertDispatched(WorkflowTransitioning::class);
    Event::assertDispatched(WorkflowTransitioned::class);
});

it('auto sets initial state when null if enabled', function (): void {
    config()->set('workflow.auto_set_initial_state', true);

    $post = TestPost::query()->create(['state' => null]);

    expect($post->workflow()->state())->toBe('draft');
    expect($post->fresh()->state)->toBe('draft');
});

it('can returns true only when state and guards allow', function (): void {
    $post = TestPost::query()->create(['state' => 'pending']);

    expect($post->workflow()->can('approve'))->toBeFalse();
    expect($post->workflow()->can('approve', null, ['can_approve' => true]))->toBeTrue();
    expect($post->workflow()->can('submit'))->toBeFalse();
});

it('available transitions are filtered by current state and guards', function (): void {
    $post = TestPost::query()->create(['state' => 'pending']);

    $withoutMeta = $post->workflow()->availableTransitions();
    $withMeta = $post->workflow()->availableTransitions(null, ['can_approve' => true]);

    expect(array_map(fn ($t) => $t->getName(), $withoutMeta))->toBe(['reject']);
    expect(array_map(fn ($t) => $t->getName(), $withMeta))->toBe(['approve', 'reject']);
});

it('throws when workflow is not configured on model', function (): void {
    $model = new TestPostWithoutWorkflow();

    $model->workflow();
})->throws(WorkflowNotFoundException::class);

it('does not write audit log when audit is disabled', function (): void {
    config()->set('workflow.audit.enabled', false);

    $post = TestPost::query()->create(['state' => 'draft']);

    $post->workflow()->apply('submit');

    expect(WorkflowTransitionLog::query()->count())->toBe(0);
});

it('uses custom state field config', function (): void {
    config()->set('workflow.state_field', 'workflow_state');

    $task = TestTask::query()->create(['workflow_state' => 'todo']);

    $task->workflow()->apply('start');

    expect($task->fresh()->workflow_state)->toBe('doing');
});

it('validates transition definitions require from states', function (): void {
    $post = TestPost::query()->create(['state' => 'draft']);

    $post->workflow(InvalidNoFromWorkflow::class)->state();
})->throws(InvalidArgumentException::class, 'must define at least one from state');

it('validates transition definitions require to state', function (): void {
    $post = TestPost::query()->create(['state' => 'draft']);

    $post->workflow(InvalidNoToWorkflow::class)->state();
})->throws(InvalidArgumentException::class, 'must define a to state');

it('validates transition definitions do not allow duplicate names', function (): void {
    $post = TestPost::query()->create(['state' => 'draft']);

    $post->workflow(InvalidDuplicateWorkflow::class)->state();
})->throws(InvalidArgumentException::class, 'Duplicate transition name');
