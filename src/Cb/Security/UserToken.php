<?php
namespace Cb\Security;

use Symfony\Component\Security\Core\User\UserInterface;

class UserToken
{
    private $user;
    private $secret = '2GfTBRga.2uPMEnEGrbeZ7';

    public function getToken(UserInterface $user)
    {
        $header = '{"alg":"HS256","typ":"JWT"}';
        date_default_timezone_set("UTC");
        $exp = time() + (7 * 24 * 60 * 60);
        $payload = array(
            'iss'      => 'carbonado',
            // token expire a week from now
            'exp'      => $exp,
            'id'       => $user->getId(),
            'username' => $user->getUsername(),
            'roles'    => $user->getRoles(),
            'projects' => $user->getProjects(),
            'company' => $user->getCompany(),
            'phone' => $user->getPhone()
        );

        $payload = json_encode($payload);
        $encodedContent = base64_encode($header) . '.' . base64_encode($payload);
        $signature = $this->sign($encodedContent);
        $result['token'] = $encodedContent . '.' . $signature;
        $result['exp'] = $exp;

        return $result;
    }

    public function parseToken($token)
    {
        if (strlen($token) == 0) {
            return array('valid' => false); 
        }
        $token = explode('.', $token);
        $encodedContent = $token[0] . '.' . $token[1];
        $result = array(
            'header'    => json_decode(base64_decode($token[0]),true),
            'payload'   => json_decode(base64_decode($token[1]),true),
            'signature' => $token[2],
            'valid'     => $token[2]==$this->sign($encodedContent)
        );

        return $result;
    }

    private function sign($content)
    {
        return hash_hmac('sha256',$content,$this->secret);
    }
}