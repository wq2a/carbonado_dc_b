<?php
namespace Cb\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Cb\Model\Repository\ApiRepository;
use Cb\Model\Entity\User;

class ApiController {

    public function action(Application $app, Request $request, $action)
    {
        $repo = new ApiRepository($app,'user');
        $res = $repo->Action($action, $app['params']);
        return $app->json($res);
    }

}
?>
