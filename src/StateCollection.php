<?php

namespace Soap\EloquentWorkflow;

use Soap\EloquentWorkflow\Traits\Injection;
use Illuminate\Support\Collection;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Support\MultipleItemsFoundException;

/**
 * @method State first(callable $callback = null, $default = null)
 */
class StateCollection extends Collection
{
    use Injection;

    public static function make($items = []): self
    {
        $collection = new static();

        foreach ($items as $item) {

            if (!($item instanceof State)) {
                $item = State::make($item);
            }

            $collection->add($item);
        }

        return $collection;
    }

    public function initial(): State
    {
        return $this->first();
    }

    /**
     * Get the exact one state from collection.
     *
     * @param \BackedEnum|string|int $state
     * @throws ItemNotFoundException
     * @throws MultipleItemsFoundException
     */
    public function one($state): State
    {
        return $this->sole(function (State $st) use ($state) {
            return $st->is($state);
        });
    }
}
