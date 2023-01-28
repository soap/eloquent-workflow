<?php

namespace Soap\EloquentWorkflow\Traits;

use Soap\EloquentWorkflow\Models\TransitionHistory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @mixin Model
 *
 * @property-read Collection|TransitionHistory[] $transitions
 */
trait HasTransitionHistory
{
    public function transitions(): MorphMany
    {
        return $this->morphMany(TransitionHistory::class, 'transitionable');
    }
}
