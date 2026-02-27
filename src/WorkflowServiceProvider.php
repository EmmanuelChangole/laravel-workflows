<?php

namespace Changole\Workflows;

use Illuminate\Support\ServiceProvider;

class WorkflowServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/workflow.php',
            'workflow'
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/workflow.php' => config_path('workflow.php'),
        ], 'workflow-config');

        if (!class_exists('CreateWorkflowTransitionLogsTable')) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_workflow_transition_logs_table.php.stub' =>
                database_path('migrations/'.date('Y_m_d_His', time()).'_create_workflow_transition_logs_table.php'),
            ], 'workflow-migrations');
        }
    }
}
