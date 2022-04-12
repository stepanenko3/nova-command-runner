<?php

namespace Stepanenko3\NovaCommandRunner\Http\Controllers;

use Stepanenko3\NovaCommandRunner\CommandService;
use Stepanenko3\NovaCommandRunner\Dto\CommandDto;
use Stepanenko3\NovaCommandRunner\Dto\RunDto;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Class CommandsController
 * @package Stepanenko3\NovaCommandRunner\Http\Controllers
 */
class CommandsController
{
    /**
     * @return array
     */
    public function index()
    {
        $data = config('nova-command-runner');
        $raw_commands = isset($data['commands']) ? $data['commands'] : [];
        $commands = [];
        if(is_array($commands)){
            foreach ($raw_commands as $label => $command ){
                $parsed = [
                    'label' => $label,
                    'command' => $command['run'],
                    'variables' => [],
                    'flags' => [],
                    'type' => isset($command['type']) ? $command['type'] : 'primary',
                    'group' => isset($command['group']) ? $command['group'] : 'Unnamed Group',
                    'help' => isset($command['help']) ? $command['help'] : 'Are you sure you want to run this command?',
                    'command_type' => isset($command['command_type']) && $command['command_type'] === 'bash' ? 'bash' : 'artisan'
                ];

                preg_match_all( '~(?<={).+?(?=})~', $command['run'], $matches );

                if( ! empty($matches[0]) ){
                    foreach ($matches[0] as $variable ){
                        $parsed['variables'][$variable] = [ 'label' => $variable, 'field' => 'text', 'placeholder' => $variable, 'value' => '' ];
                    }
                }

                if(isset($command['flags']) && is_array($command['flags'])){
                    foreach ( $command[ 'flags' ] as $flag => $label ){
                        array_push($parsed['flags'], [
                            'label' => $label,
                            'flag' => $flag,
                            'selected' => false
                        ]);
                    }
                }

                if(isset($command['variables']) && is_array($command['variables'])){
                    foreach ($command['variables'] as $variable){
                        $parsed['variables'][$variable['label']] = [
                            'label' => $variable['label'],
                            'field' => isset($variable['field']) ? $variable['field'] : 'text',
                            'value' => '',
                            'options' => isset($variable['options']) ? $variable['options'] : [],
                            'placeholder' => isset($variable['placeholder']) ? $variable['placeholder'] : $variable['label']
                        ];
                    }
                }

                array_push($commands, $parsed);
            }
        }

        $history = CommandService::getHistory();
        array_walk($history, function (&$val) {
            $val['time'] = $val['time'] ? Carbon::createFromTimestamp($val['time'])->diffForHumans() : '';
        });

        $custom_commands = [];

        if(isset($data['custom_commands']) && is_array($data['custom_commands'])){
            foreach ($data['custom_commands'] as $custom_command){
                $custom_commands[$custom_command] = ucfirst($custom_command) .' Command';
            }
        }

        return [
            'commands' => $commands,
            'history' => $history,
            'help' => isset($data['help']) ? $data['help'] : '',
            'heading' => isset($data['navigation_label']) ? $data['navigation_label'] : 'Command Runner',
            'custom_commands' => $custom_commands
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function run(Request $request)
    {
        $command = CommandDto::createFromRequest( $request );

        // Get history before running the command. Because if the user runs cache:forget command,
        // We can still have our command history after clearing the cache.
        $history = CommandService::getHistory();

        $run = CommandService::runCommand( $command, new RunDto() );

        if( $run->getCommand() === 'cache:forget nova-command-runner-history'){
            $run->setResult('Command run history has been cleared successfully.');
            $history = [ $run->toArray() ];
        } else {
            $history = array_slice($history, 0, config('nova-command-runner.history', 10) - 1);
            array_unshift($history, $run->toArray() );
        }

        CommandService::saveHistory( $history );

        array_walk($history, function (&$val) {
            $val['time'] = $val['time'] ? Carbon::createFromTimestamp($val['time'])->diffForHumans() : '';
        });

        return [ 'status' => $run->getStatus(), 'result' => nl2br( $run->getResult() ), 'history' => $history ];
    }
}
