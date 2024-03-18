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
use Throwable;

/**
 * Class RunCommand.
 */
class RunCommand implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var CommandDto
     */
    public $command;

    /**
     * @var RunDto
     */
    public $run;

    public $timeout;

    /**
     * Create a new job instance.
     */
    public function __construct(CommandDto $command, RunDto $run)
    {
        $this->command = $command;
        $this->run = $run;

        if ($command->getTimeout() !== null) {
            $this->timeout = $command->getTimeout();
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->run = CommandService::runCommand($this->command, $this->run);

        $this->updateHistory();
    }

    public function failed(Throwable $exception): void
    {
        $this->run->setStatus('error')
            ->setResult(str_replace(self::class, 'This command', $exception->getMessage()));

        $this->updateHistory();
    }

    /**
     * Update commands history.
     */
    protected function updateHistory(): void
    {
        $history = CommandService::getHistory();

        $updated_history = [];
        foreach ($history as $entry) {
            if (isset($entry['id']) && $entry['id'] === $this->run->getId()) {
                $entry = $this->run->toArray();
            }

            $updated_history[] = $entry;
        }

        CommandService::saveHistory($updated_history);
    }
}
