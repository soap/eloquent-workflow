<?php

namespace Soap\EloquentWorkflow\Traits;

use Soap\EloquentWorkflow\State;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait HasCallbacks
{
    /**
     * Callable collection, that would be invoked after event.
     *
     * @var array
     */
    protected $callbacks = [];

    /**
     * Get registered transition callbacks.
     *
     * @return Collection<callable>
     */
    public function callbacks(): Collection
    {
        return collect($this->callbacks);
    }

    /**
     * Callback(s) will run after transition is done or state is reached.
     */
    public function after(callable $callback): self
    {
        $this->callbacks[] = $callback;

        return $this;
    }

    /**
     * Run callbacks.
     *
     * @return void
     */
    public function invoke(Model $model, ?State $previous, array $context = [])
    {
        $this->callbacks()
            ->each(function (callable $callback) use ($model, $previous, $context) {
                call_user_func($callback, $model->fresh(), $previous, $context);
            });
    }
}
