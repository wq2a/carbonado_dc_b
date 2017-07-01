<?php
$CacheConfig = array(
    'caches.options' =>array(
        // cache server for totalItems
        'memcached.totalitems' => array(
            'driver'    => 'memcached',
            'memcached' => function() {
                $memcached = new \Memcached;
                $memcached->addServer('127.0.0.1', 11211);
                return $memcached;
            }
        ),
        // file cache
        'filecache.example' => array(
            'driver'    => 'file',
            'cache_dir' => './temp'
        ),
        // more cache servers here
        // file cache
        'filecache.totalitems' => array(
            'driver'    => 'file',
            'cache_dir' => './temp'
        )
    )
);
