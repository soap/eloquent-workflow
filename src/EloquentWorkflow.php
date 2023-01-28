<?php

namespace Soap\EloquentWorkflow;

use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;

class EloquentWorkflow
{
    /**
     * 
     * @return mixed 
     * @throws BindingResolutionException 
     * @throws NotFoundExceptionInterface 
     * @throws ContainerExceptionInterface 
     */
    public function getWorkflowTable()
    {
        return config('eloquent-workflow.table.workflows', 'workflows');
    }

    /**
     * 
     * @return mixed 
     * @throws BindingResolutionException 
     * @throws NotFoundExceptionInterface 
     * @throws ContainerExceptionInterface 
     */
    public function getTransitionTable()
    {
        return config('eloquent-workflow.table.transitions', 'transitions');
    }

    /**
     * 
     * @return mixed 
     * @throws BindingResolutionException 
     * @throws NotFoundExceptionInterface 
     * @throws ContainerExceptionInterface 
     */
    public function getStateTable()
    {
        return config('eloquent-workflow.table.states', 'states');
    }

    /**
     * 
     * @return mixed 
     * @throws BindingResolutionException 
     * @throws NotFoundExceptionInterface 
     * @throws ContainerExceptionInterface 
     */
    public function getStateTransitionTable()
    {
        return config('eloquent-workflow.table.state_transitions', 'state_transitions');
    }

    /**
     * 
     * @return mixed 
     * @throws BindingResolutionException 
     * @throws NotFoundExceptionInterface 
     * @throws ContainerExceptionInterface 
     */
    public function getTransitionLogTable()
    {
        return config('eloquent-workflow.table.transition_logs', 'workflows');
    }

    /**
     * 
     * @return mixed 
     * @throws BindingResolutionException 
     * @throws NotFoundExceptionInterface 
     * @throws ContainerExceptionInterface 
     */
    public function getWorkflowInstanceTable()
    {
        return config('eloquent-workflow.table.workflow_instances', 'workflow_instances');
    }
}
