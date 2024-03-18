<?php

namespace Stepanenko3\NovaCommandRunner;

use Stepanenko3\NovaCommandRunner\Dto\CommandDto;
use Stepanenko3\NovaCommandRunner\Dto\RunDto;
use Stepanenko3\NovaCommandRunner\Jobs\RunCommand;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

/**
 * Class CommandService.
 */
class CommandService
{
    /**
     * @var string
     */
    public static $TYPE_ARTISAN = 'artisan';

    /**
     * @var string
     */
    public static $TYPE_BASH = 'bash';

    /**
     * @return RunDto
     */
    public static function runCommand(CommandDto $command, RunDto $run)
    {
        if (stripos($command->getParsedCommand(), '--should-queue')) {
            [$parsed_command, $connection, $queue] = self::parseCommandForQueue($command->getParsedCommand());

            $command->setParsedCommand($parsed_command);
            $run->setType($command->getType());
            $run->setCommand($parsed_command);
            $run->setGroup($command->getGroup());

            if ($connection) {
                if ($connection !== 'sync') {
                    return self::queueCommand($command, $run, $queue, $connection);
                }
            } elseif (config('queue.default') !== 'sync') {
                return self::queueCommand($command, $run, $queue, $connection);
            }
        }

        $run->setType($command->getType());
        $run->setCommand($command->getParsedCommand());
        $run->setRanAt(now()->timestamp);
        $start = microtime(true);

        try {
            $buffer = new \Symfony\Component\Console\Output\BufferedOutput();
            if ($command->getType() === self::$TYPE_ARTISAN) {
                Artisan::call($command->getParsedCommand(), [], $buffer);
            } elseif ($command->getType() === self::$TYPE_BASH) {
                Process::fromShellCommandline($command->getParsedCommand(), base_path(), null, null, null)
                    ->run(function ($type, $message) use ($buffer): void {
                        $buffer->writeln($message);
                    });
            } else {
                throw new \Exception('Unknown command type: ' . $command->getType());
            }

            $output = $buffer->fetch();

            if ($output_size = $command->getOutputSize()) {
                $output = collect(explode("\n", $output))
                    ->filter()
                    ->reverse()
                    ->take($output_size)
                    ->reverse()
                    ->reduce(fn ($carry, $line) => $carry . "\n" . $line);
            }

            $run->setStatus('success')
                ->setResult($output);
        } catch (\Exception $exception) {
            $run->setResult($exception->getMessage());
            $run->setStatus('error');
        }

        $run->setDuration(round(microtime(true) - $start, 4));

        return $run;
    }

    /**
     * @return array
     */
    public static function parseCommandForQueue($command)
    {
        $command = str_replace('--should-queue', '', $command);

        $queue = null;
        $connection = null;

        $parsed = '';

        foreach (explode(' ', $command) as $argv) {
            if (Str::startsWith($argv, '--cr-queue=')) {
                $queue = str_replace('--cr-queue=', '', $argv);

                continue;
            }

            if (Str::startsWith($argv, '--cr-connection=')) {
                $connection = str_replace('--cr-connection=', '', $argv);

                continue;
            }

            $parsed .= ' ' . $argv;
        }

        $parsed = trim($parsed);

        return [$parsed, $connection, $queue];
    }

    /**
     * @param null $queue
     * @param null $connection
     *
     * @return RunDto
     */
    public static function queueCommand(CommandDto $command, RunDto $run, $queue = null, $connection = null)
    {
        $job = RunCommand::dispatch($command, $run)->delay(now()->addSeconds(5));

        if ($queue) {
            $job->onQueue($queue);
        }

        if ($connection) {
            $job->onConnection($connection);
        }

        $run->setStatus('pending')
            ->setResult(__('This command is queued and waiting to be processed'));

        return $run;
    }

    /**
     * @return array
     */
    public static function getHistory()
    {
        $history = Cache::get('nova-command-runner-history', []);

        return array_slice($history, 0, config('nova-command-runner.history', 10));
    }

    /**
     * @return mixed
     */
    public static function saveHistory($history)
    {
        Cache::forever('nova-command-runner-history', $history);

        return $history;
    }
}
