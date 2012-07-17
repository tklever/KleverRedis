<?php

return array(
    'service_manager' => array(
        'factories' => array(
            'klever-redis' => 'KleverRedis\Service\RedisFactory',
        ),
    ),
);
