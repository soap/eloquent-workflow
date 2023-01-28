<?php

namespace Soap\EloquentWorkflow\Traits;

use Soap\EloquentWorkflow\Contracts\Injectable;
use Soap\EloquentWorkflow\StateMachineEngine;
use Illuminate\Support\Collection;

/**
 * @mixin Collection
 */
trait Injection
{
    public function injectWith(StateMachineEngine $engine): self
    {
        return $this->each(function (Injectable $item) use ($engine) {
            $item->inject($engine);
        });
    }
}
