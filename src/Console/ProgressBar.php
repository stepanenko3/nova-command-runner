<?php

namespace Stepanenko3\NovaCommandRunner\Console;

use Illuminate\Console\OutputStyle;
use Stepanenko3\NovaCommandRunner\CommandService;
use Symfony\Component\Console\Helper\ProgressBar as BaseProgressBar;

class ProgressBar
{
    protected BaseProgressBar $progress_bar;

    protected string $command_signature;

    protected OutputStyle $output;

    public function __construct(OutputStyle $output, string $command_signature, int $max)
    {
        $this->output = $output;
        $this->progress_bar = $output->createProgressBar($max);
        $this->command_signature = $command_signature;

        $this->updateNovaCommandsHistory();
    }

    public function __call(string $name, array $arguments)
    {
        return $this->progress_bar->{$name}(...$arguments);
    }

    public function advance(int $step = 1)
    {
        $this->progress_bar->advance($step);

        $this->updateNovaCommandsHistory();

        return $this;
    }

    public function finish(): void
    {
        $this->progress_bar->finish();

        $this->updateNovaCommandsHistory();
    }

    protected function updateNovaCommandsHistory(): void
    {
        $command_updated = false;

        $command_history = array_map(function ($command) use (&$command_updated) {
            if (trim($command['run']) !== $this->command_signature || $command_updated) {
                return $command;
            }

            $command = array_merge($command, [
                'result' => $this->buildOutput(),
                'duration' => $this->progress_bar->getStartTime() ? time() - $this->progress_bar->getStartTime() : 0,
            ]);

            $command_updated = true;

            return $command;
        }, CommandService::getHistory());

        CommandService::saveHistory($command_history);
    }

    protected function buildOutput()
    {
        $regex = '{%([a-z\\-_]+)(?:\\:([^%]+))?%}i';
        $format = $this->progress_bar::getFormatDefinition(BaseProgressBar::FORMAT_NORMAL);

        return preg_replace_callback(
            array_fill(1, 4, $regex),
            fn ($matches) => call_user_func_array(
                $this->progress_bar::getPlaceholderFormatterDefinition($matches[1]),
                [$this->progress_bar, $this->output]
            ),
            $format
        );
    }
}
