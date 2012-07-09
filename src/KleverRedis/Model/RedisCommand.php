<?php

class RedisCommand {
	
	protected $_name;
	protected $_args = array();
	protected $_crlf;

	public function __construct($name, $args = array(), $crlf = NULL) {
		$this->setName($name);
		$this->setArguements($args);
		$this->setNewline($crlf);	
	}

	public function setName($name) {
		$this->_name = $name;
	}
	
	public function setArguments(array $args) {
		$this->_args = $args;
	}
	
	public function setNewline($crlf = NULL) {
		if($crlf === NULL) {
			$this->_crlf = sprintf('%s%s', chr(13), chr(10));
		} else {
			$this->_crlf = $crlf;
		}
	}
	
	public function __toString() {
		$args = $this->_args;
		array_unshift($args, strtoupper($this->_name));
		return sprintf('*%d%s%s%s', count($args), $this->_crlf, implode(array_map(array($this, 'formatArgument'), $args), $this->_crlf), $this->_crlf);
	}

	protected function formatArgument($arg) {
		return sprintf('$%d%s%s', strlen($arg), $this->_crlf, $arg);
	}
}
