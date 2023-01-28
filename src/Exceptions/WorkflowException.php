<?php

namespace Soap\EloquentWorkflow\Exceptions;

use Exception;

class WorkflowException extends Exception
{
    /**
     * @var int
     */
    public $status = 500;

    public function jsonSerialize(): array
    {
        return [
            'message' => $this->getMessage()
        ];
    }

    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()
                ->json($this->jsonSerialize(), $this->status);
        } else {
            return response($this->getMessage(), $this->status);
        }
    }
}
