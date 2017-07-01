<?php
namespace Cb;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiControllerProvider extends BaseControllerProvider {

    public function connect(Application $app)
    {
        parent::connect($app);
        $this->api->match('/user/{action}', 'Cb\\Controller\\UserController::action');
        $this->api->match('/lottery/{action}', 'Cb\\Controller\\LotteryController::action');
        $this->api->match('/{action}', 'Cb\\Controller\\ApiController::action');
        return $this->api;
    }

}

?>
