<?php
namespace App\Entity;

use App\Entity\Model\Created;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * An User can implement the same api 2 times, but with different credentials and accessToken's
 * @ORM\Entity(repositoryClass="App\Repository\UserApiRepository")
 * @ORM\Table(name="app_user_api")
 * @UniqueEntity("accessToken")
 * @ORM\HasLifecycleCallbacks
 */
class UserApi implements Created
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
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
     * @ORM\Column(type="string", length=130, nullable=true)
     */
    protected $accessToken;

    /**
     * @var string
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $isConfigured;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $created;

    function __construct()
    {
        $this->setCreated(new \DateTime());
    }

    public function getCreated() {
        return $this->created;
    }
    public function setCreated(\DateTime $dateTime = null) {
        $this->created = $dateTime;
    }
}