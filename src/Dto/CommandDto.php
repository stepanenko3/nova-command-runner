<?php

namespace Stepanenko3\NovaCommandRunner\Dto;

use Illuminate\Http\Request;

/**
 * Class CommandDto.
 */
class CommandDto
{
    private $raw_command;

    private $variables = [];

    private $flags = [];

    private $parsed_command;

    private $type;

    private $group;

    private $output_size;

    private $timeout;

    /**
     * @return CommandDto
     */
    public static function createFromRequest(Request $request)
    {
        $command = new self();
        $command->setRawCommand($request->input('command.command'));
        $command->setParsedCommand($request->input('command.command'));
        $command->setGroup($request->input('command.group'));
        $command->setOutputSize($request->input('command.output_size'));
        $command->setTimeout($request->input('command.timeout'));

        // Build command by replacing variables
        $variables = $request->input('command.variables');
        if (is_array($variables)) {
            foreach ($variables as $variable) {
                if (isset($variable['label'], $variable['value'])) {
                    if (str_contains($variable['label'], '--')) {
                        $variableName = explode('=', $variable['label'])[0];
                        $command->setParsedCommand(
                            str_replace('{' . $variable['label'] . '}', $variableName . '=' . $variable['value'], $command->getParsedCommand())
                        );
                    } else {
                        $command->setParsedCommand(
                            str_replace('{' . $variable['label'] . '}', $variable['value'], $command->getParsedCommand())
                        );
                    }
                }
            }
        }

        // Build command by adding optional flags
        $flags = $request->input('command.flags');
        if (is_array($flags)) {
            foreach ($flags as $flag) {
                if (isset($flag['selected'], $flag['flag'])   && $flag['selected']) {
                    $command->setParsedCommand($command->getParsedCommand() . ' ' . $flag['flag']);
                }
            }
        }

        $command->setType($request->input('command.command_type'));

        return $command;
    }

    /**
     * @return mixed
     */
    public function getRawCommand()
    {
        return $this->raw_command;
    }

    /**
     * @param mixed $raw_command
     */
    public function setRawCommand($raw_command): void
    {
        $this->raw_command = $raw_command;
    }

    /**
     * @return mixed
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @param mixed $variables
     */
    public function setVariables($variables): void
    {
        $this->variables = $variables;
    }

    /**
     * @return mixed
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * @param mixed $flags
     */
    public function setFlags($flags): void
    {
        $this->flags = $flags;
    }

    /**
     * @return mixed
     */
    public function getParsedCommand()
    {
        return $this->parsed_command;
    }

    /**
     * @param mixed $parsed_command
     */
    public function setParsedCommand($parsed_command): void
    {
        $this->parsed_command = $parsed_command;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

    /**
     * @return ?int
     */
    public function getOutputSize()
    {
        return $this->output_size;
    }

    /**
     * @param ?int $output_size
     */
    public function setOutputSize($output_size): void
    {
        $this->output_size = $output_size ?: false;
    }

    /**
     * @return ?int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param ?int $timeout
     */
    public function setTimeout($timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param ?string $group
     */
    public function setGroup($group): void
    {
        $this->group = $group;
    }
}
