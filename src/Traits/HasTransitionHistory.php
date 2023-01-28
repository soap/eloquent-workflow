<?php

namespace Soap\EloquentWorkflow\Traits;

use Soap\EloquentWorkflow\Models\TransitionLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @mixin Model
 *
 * @property-read Collection|TransitionLog[] $transitions
 */
trait HasTransitionLog
{
    public function transitions(): MorphMany
    {
        return $this->morphMany(TransitionLog::class, 'transitionable');
    }
}
