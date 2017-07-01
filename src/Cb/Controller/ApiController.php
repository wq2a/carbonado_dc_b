<?php
namespace Cb\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiController {

    public function action(Application $app, Request $request, $action)
    {
        return $app->json($app['params']);
        //$res = $repo->Action($action, $params);
        //return $app->json($res);
    }

}
?>
