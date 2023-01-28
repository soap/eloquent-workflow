<?php

namespace Soap\EloquentWorkflow\Models;

use Soap\EloquentWorkflow\State;
use Soap\EloquentWorkflow\StateCollection;
use Soap\EloquentWorkflow\Transition;
use Soap\EloquentWorkflow\TransitionCollection;
use Soap\EloquentWorkflow\Value;
use Soap\EloquentWorkflow\WorkflowBlueprint;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property integer $id
 * @property string $blueprint
 * @property string|null $source
 * @property string $target
 * @property array|null $context
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Authenticatable|null $performer
 * @property-read Model $transitionable
 */
class TransitionLog extends Model
{
    protected $table = 'transition_logs';

    protected $casts = [
        'context' => 'array'
    ];

    protected static function booted()
    {
        static::addGlobalScope('latest', function (Builder $builder) {
            $builder->latest();
        });
    }

    public function performer(): MorphTo
    {
        return $this->morphTo();
    }

    public function transitionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function blueprint(): ?WorkflowBlueprint
    {
        $class = $this->blueprint;

        return class_exists($class) ? new $class : null;
    }

    protected function state($value): ?State
    {
        if ($blueprint = $this->blueprint()) {
            foreach ($blueprint->states() as $state) {
                // Weak comparison
                if (Value::scalar($state) == $value) {
                    return State::make($state);
                }
            }
        }

        return null;
    }

    public function source(): ?State
    {
        if ($source = $this->source) {
            return $this->state($source);
        }

        return null;
    }

    public function target(): ?State
    {
        return $this->state($this->target);
    }

    public function transition(): ?Transition
    {
        $blueprint = $this->blueprint();

        if ($blueprint && ($source = $this->source()) && ($target = $this->target())) {
            try {

                $transition = TransitionCollection::make($blueprint->transitions())
                    ->from($source->value)
                    ->to($target->value)
                    ->sole();

                if ($context = $this->context) {
                    $transition->context($context);
                }

                return $transition;

            } catch (Exception $exception) {
                //
            }
        }

        return null;
    }
}
