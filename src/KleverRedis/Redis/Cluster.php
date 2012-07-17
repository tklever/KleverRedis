<?php

namespace KleverRedis\Redis;

use Server;
use KleverRedis\Redis\Command\CommandFilter;

class Cluster {
    
    protected $servers = array();
    protected $aliases = array();
    protected $ring = array();
    protected $replicas = 128;
    protected $filter;
    
    public function __construct($servers = array(), $replicas = null, CommandFilter $filter = null)
    {
	if(is_array($servers) && count($servers) > 0)
	{
	    $this->setServers($servers);
	}
	
	if(is_numeric($replicas)) {
	    $this->setReplicas($replicas);
	}
	if($filter !== null)
	{
	    $this->setFilter($filter);
	}
    }
    
    public function setServers($servers) {
	$this->servers = array();
	foreach($servers as $alias => $server) {
	    if(is_array($server)) {
		$server = new Server($server['host'], $server['port'])
	    }
	    
	    if(!($server instanceof Server)) {
		throw Exception('Server be an instance of Server or an array');
	    }
	    
	    $this->servers[] = $server;
	    if(is_string($alias)) {
		$this->aliases[$alias] = $this->servers[count($this->servers)-1];
	    }
	    for ($replica = 1; $replica <= $this->replicas; $replica++) {
		$this->ring[crc32($server->getHost().':'.$server->getPort().'-'.$replica)] = $this->servers[count($this->servers)-1];
	    }
	}
	ksort($this->ring, SORT_NUMERIC);
    }
    
    public function setReplicas($replicas)
    {
	$this->replicas = (int) $replicas;
    }
    
    public function getServerByAlias($alias) {
	if (isset($this->aliases[$alias])) {
	    return $this->aliases[$alias];
	} else {
	    throw new Exception("That Redisent alias does not exist");
	}
    }
    
    public function getFilter()
    {
	if(null === $this->filter) {
	    $this->filter = new CommandFilter(array(
		'RANDOMKEY',
		'DBSIZE',
		'SELECT',
		'MOVE',
		'FLUSHDB',
		'FLUSHALL',
		'SAVE',
		'BGSAVE',
		'LASTSAVE',
		'SHUTDOWN',
		'INFO',
		'MONITOR',
		'SLAVEOF'
	    ));
	}
	return $this->filter;
    }
    
    
    public function __call($name, $args) {
	if ($this->getFilter()->has($name)) {
	    $server = $this->nextNode(crc32($args[0]));
	} else {
	    $server = $this->servers[0];
    	}
	return call_user_func_array(array($server, $name), $args);
    }
    
    protected function nextNode($needle) {
	$haystack = array_keys($this->ring);
	while (count($haystack) > 2) {
	    $try = floor(count($haystack) / 2);
	    if ($haystack[$try] == $needle) {
		return $needle;
	    }
	    if ($needle < $haystack[$try]) {
		$haystack = array_slice($haystack, 0, $try + 1);
	    }
	    if ($needle > $haystack[$try]) {
		$haystack = array_slice($haystack, $try + 1);
	    }
	}
	return $this->ring[$haystack[count($haystack)-1]];
    }
}
