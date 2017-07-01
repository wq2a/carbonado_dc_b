<?php
require_once __DIR__ . '/../vendor/autoload.php';
include(__DIR__ . '/../config/database.php');
include(__DIR__ . '/../config/cache.php');

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Cb\ApiControllerProvider;
use Cb\SecureApiControllerProvider;
use Cb\UserControllerProvider;
use Cb\Security\UserProvider;
use Cb\Security\TokenAuthenticator;
use Cb\CacheServiceProvider;

$app = new Silex\Application();

$app['debug'] = true;
// Register the Doctrine DBAL DB
$app->register(new DoctrineServiceProvider(), $DBConfig);
// Register the Monolog Service
$app->register(new MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__ . '/../logs/dev.log'
));
// Register cache service, config in config/cache.php
$app->register(new CacheServiceProvider(), $CacheConfig);

$app['app.cb_token_authenticator'] = function ($app) {
    return new TokenAuthenticator($app['security.encoder_factory']);
};

// Register security firewalls
$app->register(new SecurityServiceProvider(), array(
    'security.encoders' => array(
        'AppBundle\Entity\User' => array(
            'algorithm' => 'bcrypt',
        ),
    ),
    'security.firewalls' => array(
        'login' => array(
            'pattern' => new RequestMatcher('^/login_check$',null, 'POST'),
            'guard'   => array(
                'authenticators' => array(
                    'app.cb_token_authenticator'
                )
            ),
            'users' => function() use ($app) {
                return new UserProvider($app);
            }
        ),
        'api' => array(
            'pattern'   => new RequestMatcher('^/.*$',null, ['POST','GET','OPTIONS']),
            'anonymous' => true,
        ),
     )
));

$app->get('/', function() use ($app) {
    $app['monolog']->debug(' page viewed');
    return '404'; 
});

// Mount controllers 
$app->mount('/api',new ApiControllerProvider());
$app->mount('/apisec',new SecureApiControllerProvider());

$app->get('/phpinfo', function(Request $request) use($app) {
    return phpinfo();
});

$app->match('{url}', function($url) use ($app) {
    // go to 404 page
    $app['monolog']->debug("$url page viewed");
    return $url . '404'; 
})
->method('GET|POST')
->assert('url', '.+');

$app->run();
