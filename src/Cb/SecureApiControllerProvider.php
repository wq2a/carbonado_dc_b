<?php
namespace Cb;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class SecureApiControllerProvider extends SecureControllerProvider {

    public function connect(Application $app)
    {
        parent::connect($app);
        $this->api->match('/{action}', 'Cb\\Controller\\SecureApiController::action');
        return $this->api;
    }

}

?>
