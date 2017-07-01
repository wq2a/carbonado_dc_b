<?php
namespace Cb;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class BaseControllerProvider implements ControllerProviderInterface {

    protected $api;
    public function connect(Application $app)
    {
        // get all the params
        $setParams = function (Request $request, Application $app)
        {
            if( $request->getMethod() == 'OPTIONS') {
                return new Response('1', 200);
            } else {
                if ($request->getContentType() == 'json') {
                    $data = json_decode($request->getContent(), true);
                    $app['params'] = $data;
                } else {
                    if ($request->getMethod() == 'GET'){
                        $app['params'] = $request->query->all();
                    }else if ($request->getMethod() == 'POST'){
                        $app['params'] = $request->request->all();
                    }
                }
            }
            $app['params']['ip'] = $_SERVER['REMOTE_ADDR'];
        };
        $this->api = $app['controllers_factory'];
        $this->api->before($setParams);
        return $this->api;
    }

}

?>
