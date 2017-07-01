<?php 
use Symfony\Component\DependencyInjection\Definition;
$container->loadFromExtension('security', array(
    'encoders' => array(
        'AppBundle\Entity\User' => array(
            'algorithm'         => 'bcrypt',
        ),
    ),
    'firewalls' => array(
        'main' => array(
            'pattern'             => '^/*',
            'guard'               => array(
                'authenticators'  => array(
                    'app.cb_token_authenticator'
                )
            ),
            'users' => function () use ($app) {
                return new Cb\Security\UserProvider($app['db']);
            },
    	),
    )
));
