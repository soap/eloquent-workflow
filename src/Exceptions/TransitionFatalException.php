<?php

namespace Soap\EloquentWorkflow\Exceptions;

use Throwable;

/**
 * Throws this exception to prevent transition from being in list of relevant transitions.
 */
class TransitionFatalException extends TransitionException
{
    /**
     * @var int
     */
    public $status = 403;

    public function __construct($message = "Transition is disabled", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
