<?php

namespace Soap\EloquentWorkflow\Events;

use Soap\EloquentWorkflow\StateMachineEngine;
use Soap\EloquentWorkflow\Transition;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * State value was changed.
 */
class ModelTransited
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var StateMachineEngine
     */
    public $engine;

    /**
     * @var Transition
     */
    public $transition;

    public function __construct(StateMachineEngine $engine, Transition $transition)
    {
        $this->engine = $engine;
        $this->transition = $transition;
    }
}
