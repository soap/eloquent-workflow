<?php

namespace Soap\EloquentWorkflow\Commands;

use Soap\EloquentWorkflow\BlueprintValidator;
use Soap\EloquentWorkflow\Commands\Traits\ClassDiscover;
use Soap\EloquentWorkflow\State;
use Soap\EloquentWorkflow\StateCollection;
use Soap\EloquentWorkflow\Transition;
use Soap\EloquentWorkflow\TransitionCollection;
use Soap\EloquentWorkflow\WorkflowBlueprint;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class EloquentWorkflowShowCommand extends Command
{
    use ClassDiscover;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eloquent-workflow:show {class}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display workflow scheme';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $class = $this->argument('class');
        $className = $this->classDiscover($this->argument('class'));

        if (!$className) {
            $this->error("$class Not Found");
            return self::INVALID;
        }

        $this->info($className);
        $blueprint = new $className();

        if (!($blueprint instanceof WorkflowBlueprint)) {
            $this->warn("$class Not a WorkflowBlueprint instance");
            return self::INVALID;
        }

        $transitions = TransitionCollection::make($blueprint->transitions());
        $states = StateCollection::make($blueprint->states());

        $states
            ->each(function (State $state) use ($states, $transitions) {
                $this->info($state->caption());

                $transitions->from($state->value)
                    ->each(function (Transition $transition) use ($states) {
                        $target = $states->one($transition->target)->caption();
                        $this->warn("\t{$transition->caption}->{$target}");
                    });
            });

        return self::SUCCESS;
    }

}
