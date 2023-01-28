<?php

namespace Soap\EloquentWorkflow;

use Soap\EloquentWorkflow\Commands\EloquentWorkflowShowCommand;
use Soap\EloquentWorkflow\Commands\EloquentWorkflowValidateCommand;
use Soap\EloquentWorkflow\Events\ModelInitialized;
use Soap\EloquentWorkflow\Events\ModelTransited;
use Soap\EloquentWorkflow\Listeners\TransitionListener;
use Illuminate\Support\Facades\Event;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
            //->hasViews()
            ->hasMigration('create_trasition_logs_table')
            ->hasCommands([
                EloquentWorkflowValidateCommand::class,
                EloquentWorkflowShowCommand::class
            ]);
    }

    public function bootingPackage()
    {
        if (config('eloqent-workflow.workflow.logs')) {
            Event::listen(ModelInitialized::class, [TransitionListener::class, 'handleModelInitialized']);
            Event::listen(ModelTransited::class, [TransitionListener::class, 'handleModelTransited']);
        }
    }
}
