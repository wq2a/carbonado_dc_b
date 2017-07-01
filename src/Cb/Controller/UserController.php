<?php
namespace Cb\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Cb\Model\Repository\UserRepository;
use Cb\Model\Entity\User;

class UserController {

    public function action(Application $app, Request $request, $action)
    {
        $repo = new UserRepository($app,'user');
        $params = [];
        
        $repo->rx_error_log(array("params for user request: ", $params));
        $mydata = $repo->Action($action, $params);
        return $app->json($mydata);
    }

}
?>
