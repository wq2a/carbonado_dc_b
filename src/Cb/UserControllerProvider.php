<?php
namespace Cb;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserControllerProvider extends SecureControllerProvider {

    public function connect(Application $app)
    {
        parent::connect($app);
        $this->api->match('/{action}', 'Cb\\Controller\\UserController::action');
        return $this->api;
    }

}

?>
