<?php


namespace Soap\EloquentWorkflow;

use Soap\EloquentWorkflow\Events\ModelInitialized;
use Soap\EloquentWorkflow\Events\ModelTransited;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;

/**
 * Initiates State Machine, watches for changes, fires Event.
 */
class StateMachineObserver
{
    /**
     * @var StateMachineEngine|null
     */
    protected $engine = null;

    public function __construct(?StateMachineEngine $engine = null)
    {
        $this->engine = $engine;
    }

    protected function withEngine(StateMachineEngine $engine): self
    {
        $this->engine = $engine;

        return $this;
    }

    /**
     * @param Model $model
     * @return Collection<StateMachineEngine>
     */
    private function getWorkflowListing(Model $model): Collection
    {
        $blueprints = [];

        $reflect = new ReflectionClass($model);
        foreach ($reflect->getMethods() as $method) {

            if ($method->isPublic()) {
                $return_type = $method->getReturnType();

                if ($return_type instanceof ReflectionNamedType &&
                    $return_type->getName() == StateMachineEngine::class) {
                    $blueprints[] = $method->invoke($model);
                }
            }
        }

        return collect($blueprints);
    }

    public function creating(Model $model): bool
    {
        $this->getWorkflowListing($model)
            ->each(function (StateMachineEngine $engine) use ($model) {

                // Set initial state
                $model->setAttribute($engine->attribute, $engine->getStateListing()->initial()->value);

            });

        return true;
    }

    public function created(Model $model): void
    {
        $this->getWorkflowListing($model)
            ->each(function (StateMachineEngine $engine) use ($model) {

                // Fire event
                event(new ModelInitialized($engine));

                // Run state callbacks
                $engine->state()->invoke($model, null);
            });
    }

    public function updating(Model $model): bool
    {
        // If one transition is invalid, all update is invalid
        return $this->getWorkflowListing($model)
            // Rejecting successful validations
            ->reject(function (StateMachineEngine $engine) use ($model) {

                $this->withEngine($engine);

                if ($transition = $this->nowTransiting()) {

                    // May throw an Exception
                    $transition->validate();

                    // For Transition Observer
                    if (method_exists($model, 'fireTransitionEvent')) {
                        if ($model->fireTransitionEvent('transiting', true, $engine, $transition) === false) {
                            return false;
                        }
                    }
                }

                return true;
            })
            // Empty means there are no failures
            ->isEmpty();
    }

    public function updated(Model $model): void
    {
        $this->getWorkflowListing($model)
            ->each(function (StateMachineEngine $engine) use ($model) {

                $this->withEngine($engine);

                if ($transition = $this->wasTransited()) {

                    // For Transition Observer
                    if (method_exists($model, 'fireTransitionEvent')) {
                        $model->fresh()->fireTransitionEvent('transited', false, $engine, $transition);
                    }

                    // For Event Listener
                    event(new ModelTransited($engine, $transition));

                    // Transition callbacks
                    $transition->invoke($model, $transition->source(), $transition->context());
                    // State callbacks
                    $transition->target()->invoke($model, $transition->source(), $transition->context());
                }
            });
    }

    /**
     * Get a transition, that is now running, but not saved yet.
     */
    public function nowTransiting(): ?Transition
    {
        if ($engine = $this->engine) {
            $model = $engine->model;
            $attribute = $engine->attribute;

            if ($model->isDirty($attribute) &&
                ($source = $model->getOriginal($attribute)) &&
                ($target = $model->getAttribute($attribute)) &&
                $source != $target) {

                $transition = $engine->getTransitionListing()
                    ->from($source)
                    ->to($target)
                    // Transition must exist
                    ->sole();

                // Pass context to transition for validation. May throw an Exception
                $transition->context($this->context($model, $attribute));

                return $transition;
            }
        }

        return null;
    }

    /**
     * Get a transition that was just saved.
     */
    public function wasTransited(): ?Transition
    {
        if ($engine = $this->engine) {
            $model = $engine->model;
            $attribute = $engine->attribute;

            if ($model->wasChanged($attribute) &&
                ($source = $model->getOriginal($attribute)) &&
                ($target = $model->getAttribute($attribute)) &&
                $source != $target) {

                $transition = $engine->getTransitionListing()
                    ->from($source)
                    ->to($target)
                    // Transition must exist
                    ->sole();

                // Pass context to transition, so it will be accessible in events.
                $transition->context($this->context($model, $attribute));

                return $transition;
            }
        }

        return null;
    }

    public function context(Model $model, string $attribute): array
    {
        if (property_exists($model, 'transition_context')) {
            if (isset($model->transition_context[$attribute])) {
                return $model->transition_context[$attribute];
            }
        }

        return [];
    }
}
