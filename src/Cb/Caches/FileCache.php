<?php
namespace Cb\Caches;

class FileCache extends AbstractCache
{
    private $_cacheDir;

    public function __construct(array $options = array())
    {
        if(!isset($options['cache_dir'])) {
            throw new \InvalidArgumentException('The option "cache_dir" must be passed to the FileCache constructor.');
        }
        $this->setCacheDir($options['cache_dir']);
    }

    static function isSupported()
    {
        return function_exists('file_put_contents');
    }

    /**
     * Sets the cache directory to use.
     *
     * @param string $cacheDir
     */
    public function setCacheDir($cacheDir)
    {
        if (!$cacheDir) {
            throw new \InvalidArgumentException('The parameter $cacheDir must not be empty.');
        }
        if (!is_dir($cacheDir) && !mkdir($cacheDir, 0777, true)) {
            throw new \RuntimeException('Unable to create the directory "'.$cacheDir.'"');
        }
        // remove trailing slash
        if (in_array(substr($cacheDir, -1), array('\\', '/'))) {
            $cacheDir = substr($cacheDir, 0, -1);
        }
        $this->_cacheDir = $cacheDir;
    }

    /**
     * Gets the cache directory.
     *
     * @return string
     */
    public function getCacheDir()
    {
        return $this->_cacheDir;
    }

    /**
     * Get the file name from a cache id.
     *
     * @param string $id
     */
    protected function getFileName($key)
    {
        return $this->_cacheDir . DIRECTORY_SEPARATOR . md5($key);
    }

    public function clear()
    {
        foreach (glob($this->_cacheDir . "/*") as $filename) {
            unlink($filename);
        }
        return true;
    }

    public function delete($key)
    {
        $filename = $this->getFileName($key);
        if (file_exists($filename)) {
            return unlink($filename);
        }
        return true;
    }

    public function exists($key)
    {
        return !!$this->fetch($key);
    }

    public function fetch($key)
    {
        $filename = $this->getFileName($key);
        if (!file_exists($filename)) {
            return false;
        }
        $content = unserialize(file_get_contents($filename));
        if ($this->isContentAlive($content, $filename)) {
            return $content['data'];
        }
        else {
            $this->delete($key);
        }
        return false;
    }

    public function store($key, $var = null, $ttl = 0)
    {
        $content = array('data' => $var, 'ttl' => (int) $ttl);
        return (bool) file_put_contents($this->getFileName($key), serialize($content));
    }

    protected function isContentAlive($content, $filename)
    {
        return ($content['ttl'] === 0) || ((time() - filemtime($filename)) < $content['ttl']);
    }

}
