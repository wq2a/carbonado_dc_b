<?php
namespace Cb\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class SecureApiController {

    public function action(Application $app, Request $request, $action)
    {
        return $app->json($app['params']);
        
        $repo->rx_error_log(array("params for user request: ", $params));
        $mydata = $repo->Action($action, $params);
        return $app->json($mydata);
    }

}
?>
