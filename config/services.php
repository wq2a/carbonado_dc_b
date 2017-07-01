<?php 
use Symfony\Component\DependencyInjection\Definition;

$container->setDefinition(
    'app.cb_user_provider',
    new Definition('Cb\Security\UserProvider')
);

$container->setDefinition(
    'app.cb_token_authenticator', 
    new Definition('Cb\Security\TokenAuthenticator')
);
