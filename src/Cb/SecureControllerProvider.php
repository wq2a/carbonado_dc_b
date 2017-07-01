<?php
namespace Cb;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Cb\Security\User;
use Cb\Security\UserToken;

class SecureControllerProvider extends BaseControllerProvider {

    public function connect(Application $app)
    {
        $tokenCheck = function (Request $request, Application $app)
        {
            if (!$app['params']['token']) {
                $res = array(
                    'timestamp' => time(),
                    'status' => 'fail',
                    'code'   => '403',
                    'message' => 'No token passed',
                    'data' => array()
                );  
                return new JsonResponse($res, 200);
            }
            $userToken = new UserToken();
            $result = $userToken->parseToken($token);
            if(!$result['valid']) {
                $res = array(
                    'timestamp' => time(),
                    'status' => 'fail',
                    'code'   => '403',
                    'message' => 'Invalid token',
                    'data' => array()
                ); 
                return new JsonResponse($res, 200);
            }

            if(isset($result['payload'])){
                $app['user'] = $result['payload'];
            }
        };

        parent::connect($app);
        $this->api->before($tokenCheck);
        return $this->api;
    }

}

?>
