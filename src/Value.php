<?php

namespace Soap\EloquentWorkflow;

class Value
{
    /**
     * @param State|\BackedEnum|string|integer $value
     * @return string|int
     */
    public static function scalar($value)
    {
        if ($value instanceof State) {
            $value = $value->value;
        }

        if (self::is_enum($value)) {
            return $value->value;
        } else {
            return $value;
        }
    }

    /**
     * @param State|\BackedEnum|string|integer $value
     * @return string|int
     */
    public static function name($value)
    {
        if ($value instanceof State) {
            $value = $value->value;
        }

        if (self::is_enum($value)) {
            return $value->name;
        } else {
            return $value;
        }
    }

    public static function is_enum($value): bool
    {
        return self::enum_support()
            && is_object($value)
            && enum_exists(get_class($value));
    }

    public static function enum_support(): bool
    {
        return (PHP_MAJOR_VERSION == 8 && PHP_MINOR_VERSION >= 1) ||
            PHP_MAJOR_VERSION > 8;
    }
}
