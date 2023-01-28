<?php


namespace Codewiser\Workflow\Exceptions;

use Throwable;

/**
 * User may resolve issues with transition (left instructions in the message).
 */
class TransitionRecoverableException extends TransitionException
{
    /**
     * @var int
     */
    public $status = 422;

    public function __construct($message = 'Transition is disabled', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
