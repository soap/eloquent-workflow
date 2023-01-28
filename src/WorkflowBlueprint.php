<?php

namespace Soap\EloquentWorkflow;

/**
 * Workflow blueprint.
 */
abstract class WorkflowBlueprint
{
    /**
     * Array of available Model Workflow steps. First one is initial.
     *
     * @return array<int,string,\BackedEnum,State>
     * @example [new, review, published, correcting]
     */
    abstract public function states(): array;

    /**
     * Array of allowed transitions between states.
     *
     * @return array<array<int,string,\BackedEnum>,Transition>
     * @example [[new, review], [review, published], [review, correcting], [correcting, review]]
     */
    abstract public function transitions(): array;
}
