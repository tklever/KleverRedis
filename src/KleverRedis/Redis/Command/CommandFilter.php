<?php

namespace KleverRedis\Redis\Command;

class CommandFilter {
    
    protected $commands = array();
    
    public function __construct($commands = array())
    {
	if(is_array($commands) && count($commands) > 0)
	{
	    $this->setCommands($commands);
	}
    }
    
    public function setCommands($commands)
    {
	$this->commands = array();
	foreach($commands as $command)
	{
	    $this->addCommand($command);
	}
    }
    
    public function addCommand($command) {
	$this->commands[strtoupper($command)] = $command;
    }
    
    public function has($command)
    {
	if($command instanceof Command)
	{
	    $command = $command->getName();
	}
	
	return in_array(strtoupper($command), array_keys($this->commands));
    }
}    