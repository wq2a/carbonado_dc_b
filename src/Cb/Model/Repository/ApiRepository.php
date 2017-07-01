<?php
namespace Cb\Model\Repository;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\DBAL\DBALException;
use Cb\Model\Entity\User;
use Cb\Db;

class ApiRepository extends BaseRepository {

    public function datasets()
    {
        $sets =  array(
            'register' => array(
                'title'  => 'User register',
                'short'  => 'UserRegister',
                'desc'   => 'User register',
                'db'     => 'dreamycity',
                'table'     => 'auth_user',
                'fields' => array(
                    'username', 'password', 'first_name', 'last_name', 'email'
                ),
                'func'   => 'register',
                'url'    => 'user/register'
            ),
        ); // end of sets
        ksort($sets);
        return $sets;
    }

    private $fields = array(
        'username','password','salt', 'first_name','last_name','ip_address','email'
    );

    public function register($params, $dataset)
    {
        $userParams = array();
        foreach($dataset['fields'] as $key){
            if(!isset($params[$key])) {
                return array('status' => 'error','message' => "$key is required");;
            }else{
                $userParams[$key] = $params[$key];
            }
        }
        $userParams['ip'] = $params['ip'];
        try{
            $user = new User($userParams);
            $this->conn->insert($dataset['db'].'.'.$dataset['table'], $user->getUserDbArray());
            $userParams['id'] = $this->conn->lastInsertId();
        }catch(DBALException $e){
            return array('status' => 'error','message' => "username exist");
        }
        return array($userParams);
    }

    private function existsUser($username = '')
    {
        $sql = 'select count(id) as count from users where username = ?';
        $count = $this->conn->fetchAll($sql, array($username));
        return $count[0]['count'] > 0;
    }
}
?>
