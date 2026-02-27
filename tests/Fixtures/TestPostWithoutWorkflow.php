<?php

namespace Changole\Workflows\Tests\Fixtures;

use Changole\Workflows\Traits\HasWorkflow;
use Illuminate\Database\Eloquent\Model;

class TestPostWithoutWorkflow extends Model
{
    use HasWorkflow;

    protected $table = 'test_posts';

    public $timestamps = false;

    protected $guarded = [];
}
