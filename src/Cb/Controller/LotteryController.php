<?php
namespace Cb\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Cb\Model\Repository\LotteryRepository;

class LotteryController {

    public function action(Application $app, Request $request, $action)
    {
        $repo = new LotteryRepository($app); 
        $res = $repo->Action($action, $app['params']);
        return new JsonResponse($res, 200);
    }

}
?>
