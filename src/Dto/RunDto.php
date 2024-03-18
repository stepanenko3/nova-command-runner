<?php

namespace Stepanenko3\NovaCommandRunner\Dto;

class RunDto
{
    private string $id;
    private string $command;
    private string $group;
    private string $type;
    private string $run_by;
    private string $status;
    private string $result;
    private int $duration;
    private string $ran_at;

    public function __construct()
    {
        $this->run_by = auth()->check() ? auth()->user()->name : '';
        $this->id = uniqid();
    }

    public function getRanAt(): string
    {
        return $this->ran_at;
    }

    public function setRanAt(
        string $ran_at,
    ): self {
        $this->ran_at = $ran_at;

        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(
        string $id,
    ): self {
        $this->id = $id;

        return $this;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function setCommand(
        string $command,
    ): self {
        $this->command = $command;

        return $this;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function setGroup(
        string $group,
    ): self {
        $this->group = $group;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(
        string $type,
    ): self {
        $this->type = $type;

        return $this;
    }

    public function getRunBy(): string
    {
        return $this->run_by;
    }

    public function setRunBy(
        string $run_by,
    ): self {
        $this->run_by = $run_by;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(
        string $status,
    ): self {
        $this->status = $status;

        return $this;
    }

    public function getResult(): string
    {
        return $this->result;
    }

    public function setResult(
        string $result,
    ): self {
        $this->result = $result;

        return $this;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(
        int $duration,
    ): self {
        $this->duration = $duration;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'group' => $this->group,
            'run_by' => $this->run_by,
            'run' => $this->command,
            'status' => $this->status,
            'result' => $this->result,
            'time' => $this->ran_at,
            'duration' => $this->duration,
        ];
    }
}
