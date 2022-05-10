<?php

namespace Stepanenko3\NovaCommandRunner\Dto;

/**
 * Class RunDto
 * @package Stepanenko3\NovaCommandRunner\Dto
 */
class RunDto
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $command;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $run_by;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $result;

    /**
     * @var int
     */
    private $duration;

    /**
     * @var string
     */
    private $ran_at;

    /**
     * RunDto constructor.
     */
    public function __construct()
    {
        $this->run_by = auth()->check() ? auth()->user()->name : '';
        $this->id = uniqid();
    }

    /**
     * @return string
     */
    public function getRanAt()
    {
        return $this->ran_at;
    }

    /**
     * @param $ran_at
     * @return $this
     */
    public function setRanAt($ran_at)
    {
        $this->ran_at = $ran_at;
        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param $command
     * @return $this
     */
    public function setCommand($command)
    {
        $this->command = $command;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getRunBy()
    {
        return $this->run_by;
    }

    /**
     * @param $run_by
     * @return $this
     */
    public function setRunBy($run_by)
    {
        $this->run_by = $run_by;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param $result
     * @return $this
     */
    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }

    /**
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param $duration
     * @return $this
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id'       => $this->id,
            'type'     => $this->type,
            'run_by'   => $this->run_by,
            'run'      => $this->command,
            'status'   => $this->status,
            'result'   => $this->result,
            'time'     => $this->ran_at,
            'duration' => $this->duration,
        ];
    }
}
