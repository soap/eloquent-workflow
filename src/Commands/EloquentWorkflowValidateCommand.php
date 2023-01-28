<?php

namespace Soap\EloquentWorkflow\Commands;

use Soap\EloquentWorkflow\BlueprintValidator;
use Soap\EloquentWorkflow\Commands\Traits\ClassDiscover;
use Soap\EloquentWorkflow\WorkflowBlueprint;
use Illuminate\Console\Command;

class EloquentWorkflowValidateCommand extends Command
{
    use ClassDiscover;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eloquent-workflow:validate {class=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate workflow blueprint';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $class = $this->option('class');
        $className = $this->classDiscover($this->option('class'));

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

        $validator = new BlueprintValidator($blueprint);

        $this->table(['Value', 'Caption', 'Additional', 'Error'], $validator->states());

        $this->table(['Source', 'Target', 'Caption', 'Issues', 'Auth', 'Context', 'Additional', 'Errors'], $validator->transitions());

        if ($validator->valid) {
            $this->info("Blueprint $className is valid");
            return self::SUCCESS;
        } else {
            $this->error("Blueprint $className is invalid");
            return self::FAILURE;
        }
    }

}
