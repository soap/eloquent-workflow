<?php

namespace Soap\EloquentWorkflow;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Soap\EloquentWorkflow\Commands\EloquentWorkflowCommand;

class EloquentWorkflowServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('eloquent-workflow')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_eloquent-workflow_table')
            ->hasCommand(EloquentWorkflowCommand::class);
    }
}
