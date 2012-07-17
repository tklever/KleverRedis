<?php

namespace KleverRedis\Service;

use KleverRedis\Redis\Server;
use KleverRedis\Redis\Cluster;
use KleverRedis\Redis\Command\CommandFilter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RedisFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $services)
    {
        $config = $services->get('config');
	
	if(!isset($config['klever_redis']['servers']) || count($config['klever_redis']['servers']) < 1) {
	    return new Server();
	}
	
	$servers = array();
	foreach($config['klever_redis']['servers'] as $alias => $serverConfig)
	{
	    $host = (isset($serverConfig['host']) ? $serverConfig['host'] : null);
	    $port = (isset($serverConfig['port']) ? $serverConfig['port'] : null);
	    $servers[$alias] = new Server($host, $port);
	}	
	
	if(count($servers) < 1) {
	    $replicas = null;
	    if(isset($config['klever_redis']['replicas']))
	    {
		$replicas = $config['klever_redis']['replicas'];
	    }
	    
	    $filter = null;
	    if(isset($config['klever_redis']['filter']) && is_array($config['klever_redis']['filter'])) {
		$filter = new CommandFilter($config['klever_redis']['filter']);
	    }
	    
	    return new Cluster($servers, $replicas, $filter);
	} else {
	    return array_shift($servers);
	}
    }
}
