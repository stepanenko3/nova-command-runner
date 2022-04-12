<?php
namespace Stepanenko3\NovaCommandRunner\Dto;

use Illuminate\Http\Request;

/**
 * Class CommandDto
 * @package Stepanenko3\NovaCommandRunner\Dto
 */
class CommandDto
{
    private $raw_command;

    private $variables = [];

    private $flags = [];

    private $parsed_command;


    private $type;

    /**
     * @param Request $request
     * @return CommandDto
     */
    public static function createFromRequest(Request $request )
    {
        $command = new self();
        $command->setRawCommand( $request->input('command.command') );
        $command->setParsedCommand( $request->input('command.command') );

        // Build command by replacing variables
        $variables = $request->input('command.variables');
        if( is_array($variables) ){
            foreach ($variables as $variable ){
                if( isset($variable['label']) && isset($variable['value']) ){
                    $command->setParsedCommand(
                        str_replace('{'.$variable['label'].'}', $variable['value'], $command->getParsedCommand() )
                    );
                }
            }
        }

        // Build command by adding optional flags
        $flags = $request->input('command.flags');
        if( is_array($flags) ){
            foreach ($flags as $flag){
                if(isset($flag['selected']) && isset($flag['flag']) && $flag['selected'] ){
                    $command->setParsedCommand( $command->getParsedCommand() . ' '.$flag['flag'] );
                }
            }
        }

        $command->setType( $request->input('command.command_type'));

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
    public function setRawCommand( $raw_command )
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
    public function setVariables( $variables )
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
    public function setFlags( $flags )
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
    public function setParsedCommand( $parsed_command )
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
    public function setType( $type )
    {
        $this->type = $type;
    }
}
