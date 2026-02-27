<?php

namespace Changole\Workflows\Tests\Fixtures;

use Changole\Workflows\Traits\HasWorkflow;
use Illuminate\Database\Eloquent\Model;

class TestTask extends Model
{
    use HasWorkflow;

    protected $table = 'test_tasks';

    public $timestamps = false;

    protected $guarded = [];

    protected string $workflow = TestTaskWorkflow::class;
}
