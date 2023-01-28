<?php

namespace Soap\EloquentWorkflow\Traits;

trait HasAttributes
{
    /**
     * @var array
     */
    protected $additional = [];

    /**
     * Set any additional attribute: color, order etc
     *
     * @param string $attribute
     * @param mixed $value
     *
     * @return $this
     */
    public function set(string $attribute, $value): self
    {
        $this->additional[$attribute] = $value;

        return $this;
    }

    /**
     * Get additional attributes.
     */
    public function additional(): array
    {
        return $this->additional;
    }
}
