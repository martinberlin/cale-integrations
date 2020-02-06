<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * An User can implement the same api 2 times, but with different credentials and accessToken's
 * @ORM\Entity(repositoryClass="App\Repository\UserApiRepository")
 * @ORM\Table(name="app_user_api")
 * @UniqueEntity("accessToken")
 * @ORM\HasLifecycleCallbacks
 */
class UserApi
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Api")
     * @ORM\JoinColumn(name="api_id", referencedColumnName="id")
     */
    protected $api;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $credentials;

    /**
     * @var string
     * @ORM\Column(type="string", length=130)
     */
    protected $accessToken;
}