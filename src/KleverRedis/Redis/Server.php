<?php

namespace KleverRedis\Redis;

class Server {

	protected $_sock;
	protected $_host;
	protected $_port;

	public function __construct($host = 'localhost', $port = 6379)
	{
		$this->setHost($host);
		$this->setPort($port);
	}

	public function setHost($host)
	{
		$this->_host = $host;
	}

	public function setPort($port)
	{
		$this->_port = $port;
	}

	public function getSocket()
	{
		if($this->_sock === null)
		{
			$sock = fsockopen($this->_host, $this->_port, $errno, $errstr);
			if(!$sock)
			{
				throw new Exception("{$errno} - {$errstr}");
			}
			$this->_sock = $sock;
		}
		return $this->_sock;
	}
	
	public function closeSocket()
	{
		if($this->_sock !== null)
		{
			fclose($this->_sock);
			$this->_sock = null;
		}
		return true;
	}

	public function __call($name, $args = array()) {
		return $this->execute(new Command($name, $args));
	}	

	public function execute(Command $cmd) {
		$sock = $this->getSocket();
		$command = $cms->__toString();
		for ($written = 0; $written < strlen($command); $written += $fwrite) {
			$fwrite = fwrite($sock, substr($command, $written));
			if ($fwrite === FALSE) {
				throw new Exception('Failed to write entire command to stream');
			}
		}

		$reply = trim(fgets($sock, 512));
		switch (substr($reply, 0, 1)) {
			/* Error reply */
			case '-':
				throw new Exception(substr(trim($reply), 4));
				break;
			/* Inline reply */
			case '+':
				$response = substr(trim($reply), 1);
				break;
			/* Bulk reply */
			case '$':
				$response = null;
				if ($reply == '$-1') {
					break;
				}
				$read = 0;
				$size = substr($reply, 1);
				do {
					$block_size = ($size - $read) > 1024 ? 1024 : ($size - $read);
					$response .= fread($sock, $block_size);
					$read += $block_size;
				} while ($read < $size);
				fread($sock, 2); /* discard crlf */
				break;
			/* Multi-bulk reply */
			case '*':
				$count = substr($reply, 1);
				if ($count == '-1') {
					return null;
				}
				$response = array();
				for ($i = 0; $i < $count; $i++) {
					$bulk_head = trim(fgets($sock, 512));
					$size = substr($bulk_head, 1);
					if ($size == '-1') {
						$response[] = null;
					} else {
						$read = 0;
						$block = "";
						do {
							$block_size = ($size - $read) > 1024 ? 1024 : ($size - $read);
							$block .= fread($sock, $block_size);
							$read += $block_size;
						} while ($read < $size);
						fread($sock, 2); /* discard crlf */
						$response[] = $block;
					}
				}
				break;
			/* Integer reply */
			case ':':
				$response = intval(substr(trim($reply), 1));
				break;
			default:
				throw new Exception("invalid server response: {$reply}");
				break;
		}
		$this->closeSocket();
		return $response;
	}
}
