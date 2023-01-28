<?php


namespace Soap\EloquentWorkflow;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class StateMachineEngine implements Arrayable
{
    /**
     * @var TransitionCollection|null
     */
    protected $transitions = null;

    /**
     * @var StateCollection|null
     */
    protected $states = null;

    /**
     * @var WorkflowBlueprint
     */
    public $blueprint;

    /**
     * @var Model
     */
    public $model;

    /**
     * @var string
     */
    public $attribute;

    public function __construct(WorkflowBlueprint $blueprint, Model $model, string $attribute)
    {
        $this->attribute = $attribute;
        $this->blueprint = $blueprint;
        $this->model = $model;
    }

    /**
     * Get all states of the workflow.
     *
     * @return StateCollection<State>
     */
    public function getStateListing(): StateCollection
    {
        if (!$this->states) {
            $this->states = StateCollection::make($this->blueprint->states())->injectWith($this);
        }

        return $this->states;
    }

    /**
     * Get all transitions in the workflow.
     *
     * @return TransitionCollection<Transition>
     */
    public function getTransitionListing(): TransitionCollection
    {
        if (!$this->transitions) {
            $this->transitions = TransitionCollection::make($this->blueprint->transitions())->injectWith($this);
        }

        return $this->transitions;
    }

    /**
     * Get possible transitions from the current state.
     *
     * @return TransitionCollection<Transition>
     */
    public function transitions(): TransitionCollection
    {
        return $this->state() ? $this->state()->transitions() : TransitionCollection::make();
    }

    /**
     * Change model's state to a new value, passing optional context. Returns Model for you to save it.
     *
     * @param \BackedEnum|string|int $state
     * @param array $context
     * @return Model
     */
    public function transit($state, array $context = []): Model
    {
        $this->model->setAttribute(
            $this->attribute,
            $state
        );

        // Put context for later use in observer
        if (property_exists($this->model, 'transition_context')) {
            $this->model->transition_context = [
                $this->attribute => $context
            ];
        }

        return $this->model;
    }

    /**
     * Authorize transition to the new state.
     *
     * @param \BackedEnum|string|int $target
     * @throws AuthorizationException
     */
    public function authorize($target): self
    {
        $transition = $this->transitions()
            ->to($target)
            ->sole();

        if ($ability = $transition->authorization()) {
            if (is_string($ability)) {
                Gate::authorize($ability, $this->model);
            } elseif (is_callable($ability)) {
                if (!call_user_func($ability, $this->model)) {
                    throw new AuthorizationException();
                }
            }
        }

        return $this;
    }

    /**
     * Get current state.
     */
    public function state(): ?State
    {
        $value = $this->model->getAttribute($this->attribute);

        return $value ? $this->getStateListing()->one($value) : null;
    }

    /**
     * Check if state has given value.
     *
     * @param \BackedEnum|string|int $state
     */
    public function is($state): bool
    {
        return $this->state() && $this->state()->is($state);
    }

    /**
     * Check if state doesn't have given value.
     *
     * @param \BackedEnum|string|int $state
     */
    public function isNot($state): bool
    {
        return $this->state() || $this->state()->isNot($state);
    }

    public function toArray(): array
    {
        $state = $this->state() ? $this->state()->toArray() : [];
        $transitions = $this->transitions()->onlyAuthorized()->toArray();

        return $state + ['transitions' => $transitions];
    }

    /**
     * Observer watches for transitions...
     */
    public function observer(): StateMachineObserver
    {
        return new StateMachineObserver($this);
    }

    /**
     * Get the transition from the current state, if it exists.
     *
     * @param \BackedEnum|string|int $target
     */
    public function transitionTo($target): ?Transition
    {
        return $this->state()->transitionTo($target);
    }
}
