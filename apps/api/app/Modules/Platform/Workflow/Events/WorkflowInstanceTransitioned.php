<?php

namespace App\Modules\Platform\Workflow\Events;

use App\Models\WorkflowInstance;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkflowInstanceTransitioned implements ShouldDispatchAfterCommit
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public WorkflowInstance $instance,
        public string $transition,
    ) {}
}
