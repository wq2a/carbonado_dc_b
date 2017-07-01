<?php
namespace Cb\Caches;

use Memcached;

class MemcachedCache extends AbstractCache
{
    private $_memcached;

    public function __construct(array $options = array())
    {
        if (isset($options['memcached']) && 
            is_callable($options['memcached'])) {

            $options['memcached'] = $options['memcached']();
        }

        // set default MemcacheD server if config not given
        if (!isset($options['memcached']) || !$options['memcached'] instanceof Memcached) {
            $options['memcached'] = new Memcached(uniqid());
            $options['memcached']->setOption(Memcached::OPT_COMPRESSION, false);
            $options['memcached']->addServer('127.0.0.1', 11211);
        }

        $this->setMemcached($options['memcached']);
    }

    public function setMemcached(Memcached $memcached)
    {
        $this->_memcached = $memcached;
    }

    public function getMemcached()
    {
        return $this->_memcached;
    }

    static function isSupported()
    {
        return extension_loaded('Memcached');
    }

    public function clear()
    {
        return $this->_memcahced->flush();
    }

    public function delete($key)
    {
        return $this->_memcached->delete($key);
    }

    public function fetch($key)
    {
        return $this->_memcached->get($key);
    }

    public function exists($key)
    {
        return !!$this->_memcached->get($key);
    }

    public function store($key, $value = null, $ttl = 0)
    {
        return $this->_memcached->set($key, $value, (int) $ttl);
    }
}
