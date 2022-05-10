<?php

namespace Stepanenko3\NovaCommandRunner\Jobs;

use Stepanenko3\NovaCommandRunner\CommandService;
use Stepanenko3\NovaCommandRunner\Dto\CommandDto;
use Stepanenko3\NovaCommandRunner\Dto\RunDto;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class RunCommand
 * @package Stepanenko3\NovaCommandRunner\Jobs
 */
class RunCommand implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var CommandDto
     */
    public $command;

    /**
     * @var RunDto
     */
    public $run;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(CommandDto $command, RunDto $run)
    {
        $this->command = $command;
        $this->run = $run;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->run = CommandService::runCommand($this->command, $this->run);

        $history = CommandService::getHistory();

        $updated_history = [];
        foreach ($history as $entry) {

            if (isset($entry['id']) && $entry['id'] === $this->run->getId()) {
                $entry = $this->run->toArray();
            }

            array_push($updated_history, $entry);
        }

        CommandService::saveHistory($updated_history);
    }
}
