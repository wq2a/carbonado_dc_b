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
            'list' => array(
                'title'    => 'User list',
                'short'    => 'User list',
                'desc'     => 'User list',
                'sql'      => 'select * from users_list order by first_name asc, last_name asc',
                'fields'   => '',
                'callback' => null,
                'func' => 'getUserList',
                'url'      => 'user/list'
            ),
            'role_list' => array(
                'title'    => 'Role list',
                'short'    => 'Role list',
                'desc'     => 'Role list',
                'sql'      => 'select id, name from roles',
                'fields'   => '',
                'callback' => null,
                'func' => 'getRoleList',
                'url'      => 'user/role_list'
            ),
            'project_list' => array(
                'title'    => 'Project list',
                'short'    => 'Project list',
                'desc'     => 'Project list',
                'sql'      => 'select id, name from projects',
                'fields'   => '',
                'callback' => null,
                'func' => 'getProjectList',
                'url'      => 'user/project_list'
            ),
            'add_user' => array(
                'short'    => 'Add User',
                'title'    => 'Add User',
                'desc'    => 'Add User new user',
                'func' => 'addUser',
                'url'      => 'user/project_list'
            ),
            'delete_user' => array(
                'short'    => 'Delete',
                'title'    => 'Delete  User',
                'desc'    => 'Delete user',
                'func' => 'deleteUser',
                'fields' => array(
                  'userId' => ['type' => 'int', 'required' => true],
                ),
                'url'      => 'user/delete_user',
                'name' => 'delete_user'
              ),
            'change_password' => array(
                'short'    => 'Change Password',
                'title'    => 'Change Password',
                'desc'    => 'Change Password',
                'func' => 'changePassword',
                'fields' => array(
                  'userId' => ['type' => 'int', 'required' => true],
                  'password' => ['type' => 'string', 'required' => true],
                ),
                'url'      => 'user/delete_user',
                'name' => 'delete_user'
              )
        ); // end of sets
        ksort($sets);
        return $sets;

    }

    private $fields = array(
        'username','password','salt', 'first_name','last_name',
        'ip_address','email','company', 'vuuser'
    );
    private function parseUser($account=array(), &$user=array(),
        &$roles=array())
    {
        $this->rx_error_log(array("Account: " , $account));
        foreach($account as $key=>$value){
            if(in_array($key, $this->fields)){
                $user[$key] = $value;
            }else if($key == 'edit_roles'){
                $er = $account['edit_roles'];
                $roles = [];
                $rid_proj = [];

                foreach ($er as $id => $val) {
                  list($pid, $rid) = explode('-', $id);
                  if ($rid && $pid) {
                    $rid_proj[] = ['role_id' => $rid, 'project_id' => $pid ];
                  }
                }
                $roles = $rid_proj;
            }

        }
    }

// Todo
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

    public function addUser($params, $dataset)
    {
      $account = $params['user'];
        try {
            $user = array();
            $roles = array();
            $projects = array();
            $this->parseUser($account, $user, $roles);
      $this->rx_error_log(array($user, $roles, $projects, "User account parsed. adding"));

            // Check for required params
            if(!isset($user['username'])) {
              // return error, no username
              return array('status'=>'error','message'=>'Username is required.');
            }
            // check if the username exists
            if($this->existsUser($user['username'])){
              // return error, user already exists
              $this->rx_error_log("User exists ");
              return array('status'=>'error', 'message'=>'User already exists');
            }
            // Check for password or vuuser who uses vunetid and password
            if ( (!isset($user['password']) || !$user['password'])
              && (!isset($user['vuuser']) || !$user['vuuser'] )) {
              return array('status'=>'error', 'message'=>'A password is required for non Vanderbilt users.');
            }

            // Got required params. Ok to insert user
            $user['ip_address'] = $_SERVER['REMOTE_ADDR'];

            $this->rx_error_log(['saving db user: ', $user]);
            // password and salt generate by User Class
            $userObj = new User($user);
            $user['password'] = $userObj->getPassword();
            $user['salt'] = $userObj->getSalt();

            $this->conn->insert('users', $user);
            $userId = $this->conn->lastInsertId();
            // insert into users_roles_projects
            $this->updateUserRoleProject($userId, $roles, 1);

            $account['userId'] = $userId;
            $account['roles'] = $roles;

            $account = $this->formatUserForReturn($account);
            return array('user'=>$account);

        } catch (DBALException $e) {
            $this->rx_error_log($e);
            die("Error: " . $e);
        }
    }
    public function formatUserForReturn($userArray) {
        unset($userArray['password']);
        unset($userArray['salt']);
        if ($userArray['vuuser'] == 1) {
          $userArray['vuuser'] = true;
        }
        else {
          $userArray['vuuser'] = false;
        }
        // Unset super user if not one
        //if (!$userArray['superuser']) {
         // unset($userArray['superuser']);
        //}
        $roles = [];
        $projects = [];
        foreach ($userArray['roles'] as $val) {
            if ($val) {
                $val['project'] = $this->getProjectNameById($val['project_id']);
                $val['role'] = $this->getRoleNameById($val['role_id']);
                $roles[] = $val;
            }
        }
        $userArray['roles'] = $roles;
        return $userArray;
    }

    public function changePassword($user)

    {
      // Create a new silex user which will encrypt password for us and then we
      // store salt and password in db
      $silexUser = new User($user);
      $update = [
        'password'=>$silexUser->getPassword(),
        'salt' => $silexUser->getSalt()
    ];
      $where = [
        'id' => $user['userId']
      ];
      $result = $this->conn->update('cpmv.users' , $update, $where);
      $this->rx_error_log("update result " . var_dump($result));
      return [ 'status' => 'success' , 'msg' =>  "Password updated successfully"];
    }
    public function updateUser($account)
    {
        try {
            $user = array();
            $roles = array();
            $this->parseUser($account, $user, $roles);
            $msg = '';
            $cur_user = $this->getUserByName($user['username']);

            // info updating is here
            $edit = [];

            // Check for required params
            if(!isset($user['username'])) {
              // return error, no username
              return array('status'=>'error','message'=>'Username is required.');
            }
            // check if the user exists
            if(!$cur_user){
              // return error, user already exists
              return array('status'=>'error', 'message'=>'User ' . $user['username'] . ' does not exist.');
            }
            // Check for password or vuuser who uses vunetid and password
            if ( (isset($user['password']) && $user['password'])

              && (isset($user['vuuser']) && $user['vuuser'] )) {
              return array('status'=>'error', 'message'=>'A user can not have password and be a Vanderbilt User. Vanderbilt users login with their vunetid and vunet password.');
            }
            if ( (isset($user['password']) && $user['password'])) {
              $edit['password'] = $user['password'];
              $edit['vuuser'] = 0;
              $edit['username'] = $user['email'];
              $msg .= " Password has been updated. This user logs in with their email address and specified password." ;
            }

            if (isset($user['vuuser']) && $user['vuuser'] && !$cur_user['vuuser'] ) {
              $edit['password'] = '';
              $edit['vuuser'] = $user['vuuser'];
              $msg .= " User is now a Vanderbilt user. They login with their vunetid and vunet password";
            }
            if (isset($user['vuuser']) && !$user['vuuser'] && $cur_user['vuuser'] ) {
              $edit['vuuser'] = 0 ;
              $msg .= " User is no longer a Vanderbilt user.";
            }

            if (isset($user['email']) && $user['email'] ) {
              $edit['email'] = '';

              $edit['vuuser'] = $user['vuuser'];
            }

            // Got required params. Ok to insert user

            $this->conn->insert('users', $user);
            $userId = $this->conn->lastInsertId();
            // insert into users_roles_projects
            $this->updateUserRoleProject($userId, $roles, $projects, 1);
            $account['userId'] = $userId;
            return array('user'=>$account);

        } catch (DBALException $e) {
            $this->rx_error_log($e);
            die("Error: " . $e);
        }
    }

    function getUserByName($username)
    {
        $sql = 'select * from users_list where username = ?';
        $userData = array();
        try {
            $rows = $this->conn->fetchAll($sql, array($username));

            if(!$rows){
                return NULL;
            }

            $roles = array();
            $projects = array();

            foreach ($rows as $r) {

                $roles[] = $r['role'];
                $projects[] = ['role' => $r['role'], 'role_id' => $r['role_id'], 'project_id' => $r['project_id'], 'project' => $r['project']];
            }
            $userData = $rows[0];
            $userData['roles'] = $roles;
            $userData['projects'] = $projects;

            return new User($userData);

        }catch(DBALException $e) {
            $this->rx_error_log( "Error: ".$e, 3 ,'php-errors.log');
            die( "Error: ".$e);
        }

        return NULL;
    }

    public function getRoleNameById($id)
    {
        $sql = 'select name from roles where id = ' . $id;
        try {
            return $this->conn->fetchColumn($sql);
        } catch(DBALException $e) {
            $this->rx_error_log($e);
            die("Error: " . $e);
        }
    }
    public function getProjectNameById($id)
    {
        $sql = 'select name from projects where id = ' . $id;
        try {
            return $this->conn->fetchColumn($sql);
        } catch(DBALException $e) {
            $this->rx_error_log($e);
            die("Error: " . $e);
        }
    }

    public function getUserList($params, $dataset)
    {
        $userData = array();
        try {
            $rows = $this->conn->fetchAll($dataset['sql'], array());

            if(!$rows){
                return NULL;
            }
            $users = array();
            $roles = [];
            $projects = [];
            foreach ($rows as $r) {
                // Set first  user info role
                if(!isset($users[$r['username']])) {
                    $u = array();
                    $u['userId'] = $r['id'];
                    $u['vuuser'] = $r['vuuser'] == 1 ? true : false ;
                    $u['username'] = $r['username'];
                    $u['first_name'] = $r['first_name'];
                    $u['last_name'] = $r['last_name'];
                    $u['email'] = $r['email'];
                    $u['superuser'] = $r['superuser'];
                    $u['company'] = $r['company'];
                    $u['create_date'] = $r['create_date'];
                    $u['projects'] = array( );
                    $u['roles'] = array();


                   $users[$r['username']] = $u;
                }
                else {
                  $u = $users[$r['username']];
                }
                // Add role project combo
                if ($r['project_id']) {
                  $u['projects'][]  = array(
                    'role_id'=>$r['role_id'],
                    'role'=> $r['role'],
                    'project_id' => $r['project_id'],
                    'project' => $r['project'],
                    );
                  $u['roles'][] = $r['role'];

               }
               $users[$r['username']] = $u;
            }


            return array(
                'totalItems' => sizeof($users),
                'data' => array_values($users),
            );
        }catch(DBALException $e) {
            $this->rx_error_log( "Error: ".$e, 3 ,'php-errors.log');
            die( "Error: ".$e);
        }
        return array();
    }

    public function getRoleList($params, $dataset)
    {
        // $sql = 'select id, name from roles';
        return $this->dbQuery($dataset['sql'],array(),false);
    }

    public function getProjectList($params, $dataset)
    {
        // $sql = 'select id, name from projects';
        return $this->dbQuery($dataset['sql'],array(),false);
    }

    public function addRole($role = array())
    {
        try {
            if(isset($role['name'])) {
                // check if the role exists
                if(!$this->existsRole($role['name'])){

                    $this->conn->insert('roles', $role);
                    $role['id'] = $this->conn->lastInsertId();
                    return $role;
                }else{
                    // return error, role already exists
                    return array('status'=>'error', 'message'=>'Role already exists');
                }
            }else{
                // return error, no name provided
                return array('status'=>'error','message'=>'No name provided');
            }

        } catch (DBALException $e) {
            $this->rx_error_log($e);
            die("Error: " . $e);
        }
    }

    public function addProject($project = array())
    {
        try {
            if(isset($project['name'])) {
                // check if the role exists
                if(!$this->existsProject($project['name'])){

                    $this->conn->insert('projects', $project);
                    $project['id'] = $this->conn->lastInsertId();
                    return $project;
                }else{
                    // return error, role already exists
                    return array('status'=>'error', 'message'=>'Project already exists');
                }
            }else{
                // return error, no name provided
                return array('status'=>'error','message'=>'No name provided');
            }

        } catch (DBALException $e) {
            $this->rx_error_log($e);
            die("Error: " . $e);
        }
    }

    // one time setup, add new admin
    public function addAdmin($name = 'admin', $password = 'test')
    {
        if(!$name){
            $name = 'admin';
        }
        if(!$password){
            $password = 'test';
        }

        $roleID = -1;
        $projectID = -1;
        $roleData = array(
            'name' => 'Admin',
            'description' => 'Administrator'
        );
        if($this->existsRole($roleData['name'])){
            $sql = 'select id from roles where name = ?';
            $row = $this->dbQuery($sql, array($roleData['name']),false)['data'];
            if($row[0]){
                $roleID = $row[0]['id'];
            }
        }else{
            $role = $this->addRole($roleData);
            $roleID = $role['id'];
        }

        $projectData = array(
            'name' => 'cpmv',
            'description' => 'cpmv'
        );
        if($this->existsProject($projectData['name'])){
            $sql = 'select id from projects where name = ?';
            $row = $this->dbQuery($sql, array($projectData['name']),false)['data'];
            if($row[0]){
                $projectID = $row[0]['id'];
            }
        }else{
            $project = $this->addProject($projectData);
            $projectID = $project['id'];
        }

        $userData = array(
            'username' => $name,
            'password' => $password,
            'roles'    => array($roleID => true),
            'projects' => array($projectID => true)
        );
        $user = new User($userData);
        $results = $this->addUser($user->toArray());
        if(isset($results['status']) && $results['status']=='error'){
            $results['try'] = 'http://phewas.tech/setup/add_admin?username=hwjs&password=pass';
            return $results;
        }
        $results['password'] = $password;
        return $results;
    }

    private function existsRole($name = '')
    {
        $sql = 'select count(id) as count from roles where name = ?';
        $count = $this->conn->fetchAll($sql, array($name));
        return $count[0]['count'] > 0;
    }

    private function existsProject($name = '')
    {
        $sql = 'select count(id) as count from projects where name = ?';
        $count = $this->conn->fetchAll($sql, array($name));
        return $count[0]['count'] > 0;
    }

    private function existsUser($username = '')
    {
        $sql = 'select count(id) as count from users where username = ?';
        $count = $this->conn->fetchAll($sql, array($username));
        return $count[0]['count'] > 0;
    }

    private function updateUserRoleProject($userId, $roles, $adminId)
    {
        $this->rx_error_log(array("Inserter user roles projects",  $roles));
        // Delete  access to any not in our list, untaint list
        $keep_pids = [];
        $sql = "delete from users_roles_projects where user_id = ?";
        $this->dbQuery($sql, [$userId],false);

            foreach($roles as $role ) {
                $rid = $role['role_id'];
                $pid = $role['project_id'];
                $this->rx_error_log(array("Adding project role  ", $pid, $rid));
                $relationData = array(
                    'user_id' => $userId,
                    'role_id' => $rid,
                    'project_id' => $pid,
                );
                $this->conn->insert('users_roles_projects', $relationData);
                $id = $this->conn->lastInsertId();
              }
    }
}
?>
