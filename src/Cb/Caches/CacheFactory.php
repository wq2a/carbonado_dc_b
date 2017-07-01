<?php
namespace Cb\Caches;

class CacheFactory
{
    private $drivers;
    private $options;
    public function __construct(array $drivers, array $options = array())
    {
        $this->drivers = $drivers;
        $this->options = $options;
    }

    public function getCache($driver, array $options = array())
    {
        if(!$this->driverExists($driver)) {
            throw new CacheException('The cache driver ' . $driver 
                . ' is not supported.');
        }

        $class = $this->drivers[$driver];

        if(!$class::isSupported()) {
            throw new CacheException('The cache driver ' . $driver 
                . ' is not supported by your running configuration.');
        }

        $options = array_merge($this->options, $options);

        $cache = new $class($options);

        if(!$cache instanceof CacheInterface){
            throw new CacheException('The cache driver ' . $driver 
                . ' must implement CacheInterface.');
        }

        return $cache;
    }

    public function driverExists($driver)
    {
        return isset($this->drivers[$driver]);
    }
}
