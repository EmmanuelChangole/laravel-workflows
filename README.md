# Laravel Workflows

[![Latest Version on Packagist](https://img.shields.io/packagist/v/changole/laravel-workflows.svg?style=flat-square)](https://packagist.org/packages/changole/laravel-workflows)
[![Total Downloads](https://img.shields.io/packagist/dt/changole/laravel-workflows.svg?style=flat-square)](https://packagist.org/packages/changole/laravel-workflows)
[![License](https://img.shields.io/packagist/l/changole/laravel-workflows?style=flat-square)](https://packagist.org/packages/changole/laravel-workflows)
[![Tests](https://github.com/EmmanuelChangole/laravel-workflows/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/EmmanuelChangole/laravel-workflows/actions/workflows/tests.yml)

A lightweight, Laravel-native workflow engine for Eloquent models with explicit transitions, guard validation, events, and audit history.

Supports Laravel 10, 11, and 12.

## Features

- Define explicit state transitions for Eloquent models.
- Enforce guard rules before transitions are applied.
- Emit transition lifecycle events for integrations and listeners.
- Keep audit history for transition activity.
- Configure behavior through publishable Laravel config.

## Installation

You can install the package via composer:

```bash
composer require changole/laravel-workflows
```

## Configuration

### Publishing Config and Migrations

Publish the package configuration and migrations:

```bash
php artisan vendor:publish --tag=workflow-config
php artisan vendor:publish --tag=workflow-migrations
php artisan migrate
```

### Config Example

```php
return [
    'state_field' => 'state',
    'auto_set_initial_state' => true,
    'audit' => [
        'enabled' => true,
    ],
];
```

## Usage

### Define a Workflow

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

### Attach to a Model

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

### Apply Transitions

```php
$post->workflow()->state();
$post->workflow()->can('submit');
$post->workflow()->apply('submit', auth()->user(), ['source' => 'api']);
```

## Behavior

- Guard failures dispatch `WorkflowBlocked` and throw `GuardDeniedException`.
- Successful transitions dispatch `WorkflowTransitioning` then `WorkflowTransitioned`.
- Audit entries are written to `workflow_transition_logs` when `workflow.audit.enabled = true`.

## Docker Test Workflow

```bash
make build
make install
make test
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Credits

- [Emmanuel Changole](https://github.com/EmmanuelChangole)
- [All Contributors](../../contributors)
