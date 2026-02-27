<?php

namespace Changole\Workflows\Tests;

use Changole\Workflows\WorkflowServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            WorkflowServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('test_posts', function (Blueprint $table): void {
            $table->id();
            $table->string('state')->nullable();
        });

        Schema::create('test_tasks', function (Blueprint $table): void {
            $table->id();
            $table->string('workflow_state')->nullable();
        });

        Schema::create('workflow_transition_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('workflow');
            $table->string('transition');
            $table->string('model_type');
            $table->string('model_id');
            $table->string('from_state');
            $table->string('to_state');
            $table->string('actor_type')->nullable();
            $table->string('actor_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }
}
