<?php
namespace Cb;

use Silex\Application;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Cb\Caches\CacheFactory;

class CacheServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{
    public function register(Container $app)
    {
        $app['cache.default_options'] = array(
            'driver' => 'array'
        );

        $app['cache.drivers'] = function () {
            return array(
                'memcached' => '\\Cb\\Caches\\MemcachedCache',
                'file'      => '\\Cb\\Caches\\FileCache',
            );
        };

        $app['cache.factory'] = $app->factory(function ($app) {
            return new CacheFactory($app['cache.drivers'], $app['caches.options']);
        });

        $app['caches.options.initializer'] = $app->protect(function () use ($app) {
            static $initialized = false;
            if ($initialized) {
                return;
            }
            $initialized = true;
            if (!isset($app['caches.options'])) {
                $app['caches.options'] = array('default' => isset($app['cache.options']) ? $app['cache.options'] : array());
            }
            $tmp = $app['caches.options'];
            foreach ($tmp as $name => &$options) {
                $options = array_replace($app['cache.default_options'], $options);
                if (!isset($app['caches.default'])) {
                    $app['caches.default'] = $name;
                }
            }
            $app['caches.options'] = $tmp;
        });

        $app['caches'] = $app->factory(function ($app) {
            $app['caches.options.initializer']();
            $caches = new Container();
            foreach ($app['caches.options'] as $name => $options) {
                if ($app['caches.default'] === $name) {
                    // we use shortcuts here in case the default has been overridden
                    $config = $app['cache.config'];
                } else {
                    $config = $app['caches.config'][$name];
                }
                $caches[$name] = $caches->factory(function ($caches) use ($app, $config) {
                    return $app['cache.factory']->getCache($config['driver'], $config);
                });
            }
            return $caches;
        });

        $app['caches.config'] = $app->factory(function ($app) {
            $app['caches.options.initializer']();
            $configs = new Container();
            foreach ($app['caches.options'] as $name => $options) {
                $configs[$name] = $options;
            }
            return $configs;
        });

        // shortcuts for the "first" cache
        $app['cache'] = $app->factory(function ($app) {
            $caches = $app['caches'];
            return $caches[$app['caches.default']];
        });

        $app['cache.config'] = $app->factory(function ($app) {
            $caches = $app['caches.config'];
            return $caches[$app['caches.default']];
        });
    }

    public function boot(Application $app)
    {

    }

}
