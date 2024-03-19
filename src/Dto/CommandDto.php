<?php

namespace Stepanenko3\NovaCommandRunner\Dto;

use Illuminate\Http\Request;

class CommandDto
{
    private string $raw_command;

    private array $variables = [];

    private array $flags = [];

    private string $parsed_command;

    private string $type;

    private ?string $group;

    private ?int $output_size;

    private ?int $timeout;

    public static function createFromRequest(
        Request $request,
    ): self {
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

    public function getRawCommand(): string
    {
        return $this->raw_command;
    }

    public function setRawCommand(
        string $raw_command,
    ): void {
        $this->raw_command = $raw_command;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function setVariables(
        array $variables,
    ): void {
        $this->variables = $variables;
    }

    public function getFlags(): array
    {
        return $this->flags;
    }

    public function setFlags(
        array $flags
    ): void {
        $this->flags = $flags;
    }

    public function getParsedCommand(): string
    {
        return $this->parsed_command;
    }

    public function setParsedCommand(
        string $parsed_command,
    ): void {
        $this->parsed_command = $parsed_command;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(
        string $type,
    ): void {
        $this->type = $type;
    }

    public function getOutputSize(): int | bool | null
    {
        return $this->output_size;
    }

    public function setOutputSize(
        ?int $output_size,
    ): void {
        $this->output_size = $output_size ?: false;
    }

    public function getTimeout(): ?int
    {
        return $this->timeout;
    }

    public function setTimeout(
        ?int $timeout,
    ): void {
        $this->timeout = $timeout;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function setGroup(
        ?string $group,
    ): void {
        $this->group = $group;
    }
}
