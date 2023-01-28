<?php

namespace Soap\EloquentWorkflow\Traits;

trait HasCaption
{
    /**
     * @var string|null
     */
    public $caption = null;

    /**
     * Set State caption.
     */
    public function as(string $caption): self
    {
        if ($caption)
            $this->caption = $caption;

        return $this;
    }

    /**
     * Get caption of the State.
     */
    abstract public function caption(): string;
}
