<?php

namespace Soap\EloquentWorkflow\Events;

use Soap\EloquentWorkflow\StateMachineEngine;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Model got initial state value.
 */
class ModelInitialized
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var StateMachineEngine
     */
    public $engine;

    public function __construct(StateMachineEngine $engine)
    {
        $this->engine = $engine;
    }
}
