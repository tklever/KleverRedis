<?php

namespace KleverRedis\Service;

use KleverRedis\Redis\Server;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RedisServerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $services)
    {
        $config = $services->get('config');
	$host = null;
	$port = null;
	if(isset($config['klever_redis'] && count($config['klever_redis']) > 0))
	{
		$serverConfig = array_shift($config['klever_redis']);
		$host = (isset($serverConfig['host']) ? $serverConfig['host'] : null);
		$port = (isset($serverConfig['port']) ? $serverConfig['port'] : null);
	}	

	$service = new Server($host, $port);
        return $service;
    }
}
