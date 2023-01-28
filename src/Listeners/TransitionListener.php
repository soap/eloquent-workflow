<?php

namespace Soap\EloquentWorkflow\Listeners;

use Soap\EloquentWorkflow\Events\ModelInitialized;
use Soap\EloquentWorkflow\Events\ModelTransited;
use Soap\EloquentWorkflow\Models\TransitionLog;
use Soap\EloquentWorkflow\State;
use Soap\EloquentWorkflow\StateMachineEngine;
use Soap\EloquentWorkflow\Value;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class TransitionListener
{
    protected function newRecordFor(Model $model, StateMachineEngine $engine): TransitionLog
    {
        $log = new TransitionLog;

        $log->transitionable()->associate($model);
        $log->blueprint = get_class($engine->blueprint);

        if (($user = auth()->user()) && ($user instanceof Model)) {
            $log->performer()->associate($user);
        }

        return $log;
    }

    public function handleModelInitialized(ModelInitialized $event): void
    {
        $log = $this->newRecordFor($event->engine->model, $event->engine);

        $log->target = Value::scalar(
            $event->engine->state()
        );

        $log->save();
    }

    public function handleModelTransited(ModelTransited $event): void
    {
        $log = $this->newRecordFor($event->engine->model, $event->engine);

        $log->source = Value::scalar(
            $event->transition->source()
        );

        $log->target = Value::scalar(
            $event->transition->target()
        );

        try {
            if ($context = $event->transition->context()) {
                $log->context = $context;
            }
        } catch (ValidationException $exception) {
            // Actually it was successfully validated...
        }

        $log->save();
    }
}
