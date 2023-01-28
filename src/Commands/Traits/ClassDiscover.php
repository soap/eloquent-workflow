<?php

namespace Soap\EloquentWorkflow\Commands\Traits;

use Illuminate\Support\Str;

trait ClassDiscover
{

    protected function classDiscover(string $class): ?string
    {
        $class = (string)Str::of($class)->replace('/', '\\');
        if (class_exists($class)) {
            return $class;
        }

        $className = "App\\{$class}";
        if (class_exists($className)) {
            return $className;
        }

        $className = "App\\Workflow\\{$class}";
        if (class_exists($className)) {
            return $className;
        }

        $className = "App\\Models\\Workflow\\{$class}";
        if (class_exists($className)) {
            return $className;
        }

        return null;
    }
}
