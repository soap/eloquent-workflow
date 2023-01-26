<?php

namespace Soap\EloquentWorkflow\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Soap\EloquentWorkflow\EloquentWorkflow
 */
class EloquentWorkflow extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Soap\EloquentWorkflow\EloquentWorkflow::class;
    }
}
