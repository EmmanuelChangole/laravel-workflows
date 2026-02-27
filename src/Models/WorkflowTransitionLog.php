<?php

namespace Changole\Workflows\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowTransitionLog extends Model
{
    protected $table = 'workflow_transition_logs';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime',
    ];
}
