<?php
namespace Cb\Model\Repository;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\DBAL\DBALException;
use Cb\Model\Entity\User;
use Cb\Db;

class UserRepository extends BaseRepository {

    public function datasets()
    {
        $sets =  array(
            'register' => array(
                'title'  => 'User register',
                'short'  => 'UserRegister',
                'desc'   => 'User register',
                'fields' => '',
                'func'   => 'register',
                'url'    => 'user/register'
            ),
            'group_list' => array(
                'title'  => 'Group list',
                'short'  => 'Group list',
                'desc'   => 'Group list',
                'sql'    => 'select id, name from auth_group',
                'fields' => '',
                'url'    => 'user/group_list'
            ),
            'delete_user' => array(
                'short'  => 'Delete',
                'title'  => 'Delete  User',
                'desc'   => 'Delete user',
                'func'   => 'deleteUser',
                'url'    => 'user/delete_user',
                'name'   => 'delete_user',
                'fields' => array(
                    'userId' => ['type' => 'int', 'required' => true]
                )
            ),
            'change_password' => array(
                'short'  => 'Change Password',
                'title'  => 'Change Password',
                'desc'   => 'Change Password',
                'func'   => 'changePassword',
                'url'    => 'user/delete_user',
                'name'   => 'delete_user',
                'fields' => array(
                    'userId'   => ['type' => 'int', 'required' => true],
                    'password' => ['type' => 'string', 'required' => true]
                )
            )
        ); // end of sets
        ksort($sets);
        return $sets;
    }

    private $fields = array(
        'username','password','salt', 'first_name','last_name','ip_address','email'
    );

    public function deleteUser($params, $dataset)
    {
      $userId = $params['userId'];
      if (!$userId) { die ("User id not passed in to delete"); }
      $sql = "delete from users_roles_projects where user_id = ?";
      $this->dbQuery($sql, array($userId));
      $sql = "delete from users where id = ?";
      $this->dbQuery($sql, array($userId));
      return "User $userId deleted";
    }

    public function register($params, $dataset)
    {
        $account = $params['user'];
        return array('user'=>$account);
//        try {
//            $user = array();
//            $this->parseUser($account, $user, $roles);
//
//            // Check for required params
//            if(!isset($user['username'])) {
//              // return error, no username
//              return array('status'=>'error','message'=>'Username is required.');
//            }
//            // check if the username exists
//            if($this->existsUser($user['username'])){
//              // return error, user already exists
//              $this->rx_error_log("User exists ");
//              return array('status'=>'error', 'message'=>'User already exists');
//            }
//            // Check for password or vuuser who uses vunetid and password
//            if ( (!isset($user['password']) || !$user['password'])
//              && (!isset($user['vuuser']) || !$user['vuuser'] )) {
//              return array('status'=>'error', 'message'=>'A password is required for non Vanderbilt users.');
//            }
//
//            // Got required params. Ok to insert user
//            $user['ip_address'] = $_SERVER['REMOTE_ADDR'];
//
//            $this->rx_error_log(['saving db user: ', $user]);
//            // password and salt generate by User Class
//            $userObj = new User($user);
//            $user['password'] = $userObj->getPassword();
//            $user['salt'] = $userObj->getSalt();
//
//            $this->conn->insert('users', $user);
//            $userId = $this->conn->lastInsertId();
//            // insert into users_roles_projects
//            $this->updateUserRoleProject($userId, $roles, 1);
//
//            $account['userId'] = $userId;
//            $account['roles'] = $roles;
//
//            $account = $this->formatUserForReturn($account);
//            return array('user'=>$account);
//
//        } catch (DBALException $e) {
//            $this->rx_error_log($e);
//            die("Error: " . $e);
//        }
    }
    private function existsUser($username = '')
    {
        $sql = 'select count(id) as count from users where username = ?';
        $count = $this->conn->fetchAll($sql, array($username));
        return $count[0]['count'] > 0;
    }
}
?>
