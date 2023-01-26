<?php

namespace Soap\EloquentWorkflow\Commands;

use Illuminate\Console\Command;

class EloquentWorkflowCommand extends Command
{
    public $signature = 'eloquent-workflow';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
