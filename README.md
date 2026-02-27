# changole/laravel-workflows

A lightweight, Laravel-native workflow engine for Eloquent models with explicit transitions, guard validation, events, and audit history.

## Installation

```bash
composer require changole/laravel-workflows
```

Publish config and migration:

```bash
php artisan vendor:publish --tag=workflow-config
php artisan vendor:publish --tag=workflow-migrations
php artisan migrate
```

## Quick Start

Define a workflow:

```php
<?php

namespace App\Workflows;

use App\Models\Post;
use Changole\Workflows\Core\WorkflowDefinition;
use Changole\Workflows\Core\WorkflowContext;

class PostWorkflow extends WorkflowDefinition
{
    public function model(): string
    {
        return Post::class;
    }

    public function initialState(): string
    {
        return 'draft';
    }

    public function transitions(): array
    {
        return [
            $this->transition('submit')->from('draft')->to('pending'),
            $this->transition('approve')
                ->from('pending')
                ->to('approved')
                ->guard(fn (WorkflowContext $ctx) => (bool) ($ctx->meta['can_approve'] ?? false), 'Not allowed'),
            $this->transition('reject')->from('pending')->to('rejected'),
        ];
    }
}
```

Attach to a model:

```php
<?php

namespace App\Models;

use Changole\Workflows\Traits\HasWorkflow;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasWorkflow;

    protected string $workflow = \App\Workflows\PostWorkflow::class;
}
```

Use it:

```php
$post->workflow()->state();
$post->workflow()->can('submit');
$post->workflow()->apply('submit', auth()->user(), ['source' => 'api']);
```

## Behavior

- Guard failures dispatch `WorkflowBlocked` and throw `GuardDeniedException`.
- Successful transitions dispatch `WorkflowTransitioning` then `WorkflowTransitioned`.
- Audit entries are written to `workflow_transition_logs` when `workflow.audit.enabled = true`.

## Config

```php
return [
    'state_field' => 'state',
    'auto_set_initial_state' => true,
    'audit' => [
        'enabled' => true,
    ],
];
```

## Docker Test Workflow

```bash
make build
make install
make test
```
