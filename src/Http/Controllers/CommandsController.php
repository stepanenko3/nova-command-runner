<?php

namespace Stepanenko3\NovaCommandRunner\Http\Controllers;

use Stepanenko3\NovaCommandRunner\CommandService;
use Stepanenko3\NovaCommandRunner\Dto\CommandDto;
use Stepanenko3\NovaCommandRunner\Dto\RunDto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Class CommandsController.
 */
class CommandsController
{
    public function show()
    {
        return inertia('NovaCommandRunner');
    }

    /**
     * @return array
     */
    public function index()
    {
        $data = config('nova-command-runner');
        $raw_commands = $data['commands'] ?? [];
        $commands = [];
        if (is_array($commands)) {
            foreach ($raw_commands as $label => $command) {
                $parsed = [
                    'label' => $label,
                    'command' => $command['run'],
                    'variables' => [],
                    'flags' => [],
                    'type' => $command['type'] ?? 'primary',
                    'group' => $command['group'] ?? 'Unnamed Group',
                    'help' => $command['help'] ?? 'Are you sure you want to run this command?',
                    'command_type' => isset($command['command_type']) && $command['command_type'] === 'bash' ? 'bash' : 'artisan',
                    'output_size' => $command['output_size'] ?? null,
                    'timeout' => $command['timeout'] ?? null,
                    'unique' => $command['unique'] ?? false,
                ];

                preg_match_all('~(?<={).+?(?=})~', $command['run'], $matches);

                if (!empty($matches[0])) {
                    foreach ($matches[0] as $variable) {
                        $parsed['variables'][$variable] = ['label' => $variable, 'field' => 'text', 'placeholder' => $variable, 'value' => ''];
                    }
                }

                if (isset($command['flags']) && is_array($command['flags'])) {
                    foreach ($command['flags'] as $flag => $label) {
                        $parsed['flags'][] = [
                            'label' => $label,
                            'flag' => $flag,
                            'selected' => false,
                        ];
                    }
                }

                if (isset($command['variables']) && is_array($command['variables'])) {
                    foreach ($command['variables'] as $variable) {
                        $parsed['variables'][$variable['label']] = [
                            'label' => $variable['label'],
                            'field' => $variable['field'] ?? 'text',
                            'value' => '',
                            'options' => $variable['options'] ?? [],
                            'placeholder' => $variable['placeholder'] ?? $variable['label'],
                        ];
                    }
                }

                $commands[] = $parsed;
            }
        }

        $history = CommandService::getHistory();
        array_walk($history, function (&$val): void {
            $val['time'] = $val['time'] ? Carbon::createFromTimestamp($val['time'])->diffForHumans() : '';
        });

        $custom_commands = [];

        if (isset($data['custom_commands']) && is_array($data['custom_commands'])) {
            foreach ($data['custom_commands'] as $custom_command) {
                $custom_commands[$custom_command] = ucfirst($custom_command) . ' Command';
            }
        }

        return [
            'commands' => $commands,
            'history' => $history,
            'help' => $data['help'] ?? '',
            'heading' => $data['navigation_label'] ?? 'Command Runner',
            'custom_commands' => $custom_commands,
            'polling_time' => config('nova-command-runner.polling_time', 1000),
        ];
    }

    /**
     * @return array
     */
    public function run(Request $request)
    {
        $command = CommandDto::createFromRequest($request);

        // Get history before running the command. Because if the user runs cache:forget command,
        // We can still have our command history after clearing the cache.
        $history = CommandService::getHistory();

        if (!$this->commandCanBeRun($command, $history)) {
            return [
                'status' => 'error',
                'result' => 'Try running this command later.',
                'history' => $history,
            ];
        }

        $run = CommandService::runCommand($command, new RunDto());

        if ($run->getCommand() === 'cache:forget nova-command-runner-history') {
            $run->setResult('Command run history has been cleared successfully.');
            $history = [$run->toArray()];
        } else {
            $history = array_slice($history, 0, config('nova-command-runner.history', 10) - 1);
            array_unshift($history, $run->toArray());
        }

        CommandService::saveHistory($history);

        array_walk($history, function (&$val): void {
            $val['time'] = $val['time'] ? Carbon::createFromTimestamp($val['time'])->diffForHumans() : '';
        });

        return [
            'status' => $run->getStatus(),
            'result' => nl2br($run->getResult()),
            'history' => $history,
        ];
    }

    /**
     * @return bool
     */
    protected function commandCanBeRun(CommandDto $command, $history)
    {
        $group = $command->getGroup();
        [$command] = CommandService::parseCommandForQueue($command->getParsedCommand());

        $groupPatterns = $this->getMatchedPatterns(
            $group,
            config('nova-command-runner.without_overlapping.groups', []),
        );

        $commandPatterns = $this->getMatchedPatterns(
            $command,
            config('nova-command-runner.without_overlapping.commands', []),
        );

        if ($groupPatterns === false && $commandPatterns === false) {
            return true;
        }

        return collect($history)
            ->filter(function ($entry) use ($groupPatterns, $commandPatterns) {
                $condition = ($groupPatterns !== false && in_array_wildcard($entry['group'], $groupPatterns))
                    || ($commandPatterns !== false && in_array_wildcard($entry['run'], $commandPatterns));

                return Arr::get($entry, 'status') === 'pending' && $condition;
            })
            ->isEmpty();
    }

    /**
     * getMatchedPatterns.
     *
     * @param mixed $needle
     * @param array $array
     */
    private function getMatchedPatterns($needle, array $haystack): array | false
    {
        $patterns = [];
        foreach ($haystack as $pattern) {
            if (Str::is($pattern, $needle)) {
                $patterns[] = $pattern;
            }
        }

        if (count($patterns) > 0) {
            return $patterns;
        }

        return false;
    }
}
