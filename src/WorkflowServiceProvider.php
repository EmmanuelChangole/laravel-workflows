<?php

namespace Changole\Workflows;

use Changole\Workflows\Audit\EloquentAuditor;
use Changole\Workflows\Contracts\Auditor;
use Illuminate\Support\ServiceProvider;

class WorkflowServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/workflow.php', 'workflow');

        $this->app->bind(Auditor::class, EloquentAuditor::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/workflow.php' => config_path('workflow.php'),
        ], 'workflow-config');

        if (! class_exists('CreateWorkflowTransitionLogsTable')) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_workflow_transition_logs_table.php.stub' =>
                database_path('migrations/'.date('Y_m_d_His').'_create_workflow_transition_logs_table.php'),
            ], 'workflow-migrations');
        }
    }
}
