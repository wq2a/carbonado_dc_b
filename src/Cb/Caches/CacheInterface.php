<?php
namespace Cb\Caches;

interface CacheInterface
{
    /**
     * Invalidate all items in the cache
     *
     * @return bool return true on success
     **/
    public function clear();

    /**
     * Delete item
     *
     * @param delete item with $key
     * @return bool return true on success
     **/
    public function delete($key);

    /**
     * Fetch item
     *
     * @param fetch item with $key
     * @return item value
     **/
    public function fetch($key);

    /**
     * Store item
     *
     * @param $key
     * @param $value
     * @param $ttl the expiration time
     **/
    public function store($key, $value, $ttl);

    /**
     * check if item exists
     *
     * @param check item with $key
     * @return bool return true if exists
     **/
    public function exists($key);

    /**
     * check if cache driver is supported
     *
     * @return bool return true if supported
     **/
    public static function isSupported();
}
