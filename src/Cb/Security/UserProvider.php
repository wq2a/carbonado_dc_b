<?php
// src/Cpm/Security/UserProvider.php
namespace Cb\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Silex\Application;
use Doctrine\DBAL\Connection;
// use Cb\UserDb;
use Cb\Model\Repository\UserRepository;

class UserProvider implements UserProviderInterface
{
    private $conn;
    private $userRepo;

    public function __construct (Application $app)
    {
        $this->conn = $app['db']; 
        $this->userRepo = new UserRepository($app);
    }

    public function loadUserByUsername($username)
    {
        // make a call to your DB here
        $user = $this->userRepo->getUserByName($username);
        if ($user) { 
            return $user;
        }

        throw new UsernameNotFoundException(
            sprintf('Username "%s" does not exist.', $username)
        );
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'Cpm\Security\User';
    }
}
?>
