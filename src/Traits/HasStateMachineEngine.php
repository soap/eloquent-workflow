<?php

namespace Soap\EloquentWorkflow\Traits;

use Soap\EloquentWorkflow\StateMachineEngine;

trait HasStateMachineEngine
{
    /**
     * @var StateMachineEngine|null
     */
    protected $engine = null;

    public function inject(StateMachineEngine $engine)
    {
        $this->engine = $engine;

        return $this;
    }

    /**
     * Method will fail if object was not injected before — it is ok.
     */
    public function engine(): StateMachineEngine
    {
        return $this->engine;
    }
}
