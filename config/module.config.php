<?php

return array(
    'service_manager' => array(
        'factories' => array(
            'redis-server' => 'KleverRedis\Service\RedisServerFactory',
        ),
    ),
);
