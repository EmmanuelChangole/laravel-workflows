<?php

namespace Changole\Workflows\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Changole\Workflows\WorkflowServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            WorkflowServiceProvider::class,
        ];
    }
}
