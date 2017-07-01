<?php 
namespace Cb\Model\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface 
{
    private $id;
    private $username;
    private $password;
    private $salt;
    private $email;
    private $first_name;
    private $last_name;
    private $groups;

    private $create_date;
    private $superuser;

    public function __construct(array $user)
    {
        $this->id = isset($user['id'])?$user['id']:'';
        $this->username = isset($user['username'])?$user['username']:'';

        if(!isset($user['salt'])){
            $this->salt = $this->_getSalt();
            $this->password = $this->_getPassword(
                isset($user['password'])?$user['password']:'');
        }else{
            $this->salt = isset($user['salt'])?$user['salt']:'';
            $this->password = isset($user['password'])?$user['password']:'';
        }

        $this->email = isset($user['email'])?$user['email']:'';
        $this->first_name = isset($user['first_name'])?$user['first_name']:'';
        $this->last_name = isset($user['last_name'])?$user['last_name']:'';
        $this->groups = isset($user['groups'])?$user['groups']:array();

        $this->create_date = isset($user['create_date'])?$user['create_date']:0;
        $this->superuser = isset($user['superuser'])?$user['superuser']:0;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    private function _getSalt()
    {
        $salt = substr(base64_encode(openssl_random_pseudo_bytes(17)),0,22);
        return str_replace('+','.',$salt);
    }

    private function _getPassword($password)
    {
        return hash('sha256', $password . $this->salt);
    }
   
    public function getRoles()
    {
        return $this->roles;
    }

    public function getProjects()
    {
        return $this->projects;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getSalt()
    {
        return $this->salt;
    }
    public function getUsername()
    {
        return $this->username;
    }
 
    public function getCreatedate()
    {
        return $this->create_date;
    }
    public function getSuperuser()
    {
        return $this->superuser;
    }

    public function eraseCredentials()
    {
    }

    public function getUserDbArray()
    {
        return array(
            'username'   => $this->username,
            'password'   => $this->password,
            'salt'       => $this->salt,
            'email'      => $this->email,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'create_date'  => $this->create_date,
            'superuser'  => $this->superuser
        );
    }

    public function toArray()
    {
        return array(
            'username'   => $this->username,
            'email'      => $this->email,
            'groups'      => $this->groups,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'salt'       => $this->salt,
            'password'   => $this->password,
            'create_date'  => $this->create_date,
            'superuser'  => $this->superuser
        );
    }

    public function isValid(array $user)
    {
        if (!isset($user['password'])
             || $this->password !== $this->_getPassword($user['password'])) {
            return false;
        }
        return true;
    }
}
?>
