<?php

namespace Changole\Workflows\Tests\Fixtures;

use Changole\Workflows\Traits\HasWorkflow;
use Illuminate\Database\Eloquent\Model;

class TestPost extends Model
{
    use HasWorkflow;

    protected $table = 'test_posts';

    public $timestamps = false;

    protected $guarded = [];

    protected string $workflow = TestPostWorkflow::class;
}
