<?php
namespace App\Entity;

use App\Entity\Model\Created;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

// Disable unique for testing with same darksky Api
// @UniqueEntity("accessToken")
/**
 * An User can implement the same api 2 times, but with different accessToken's
 * @ORM\Entity(repositoryClass="App\Repository\UserApiRepository")
 * @ORM\Table(name="app_user_api")
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
     * @ORM\Column(type="text", nullable=true)
     */
    protected $credentials;

    /**
     * @var string
     * @ORM\Column(type="string", length=130, nullable=true)
     */
    protected $accessToken;

    /**
     * @var string
     * @ORM\Column(type="boolean")
     */
    protected $isConfigured;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updated;

    function __construct()
    {
        $this->setIsConfigured(false);
        $this->setCreated(new \DateTime());
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function updateModifiedDatetime() {
        // update the modified time
        $this->setUpdated(new \DateTime());
    }

    public function setUpdated(\DateTime $dateTime = null) {
        $this->uddated = $dateTime;
    }

    public function getUpdated() {
        return $this->updated;
    }


    public function getCreated() {
        return $this->created;
    }
    public function setCreated(\DateTime $dateTime = null) {
        $this->created = $dateTime;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * @param mixed $api
     */
    public function setApi($api)
    {
        $this->api = $api;
    }

    /**
     * @return string
     */
    public function getCredentials(): string
    {
        return $this->credentials;
    }

    /**
     * @param string $credentials
     */
    public function setCredentials(string $credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * @return string
     */
    public function getAccessToken():?string
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     */
    public function setAccessToken(string $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @return string
     */
    public function isConfigured():? bool
    {
        return $this->isConfigured;
    }

    /**
     * @param string $isConfigured
     */
    public function setIsConfigured(string $isConfigured)
    {
        $this->isConfigured = $isConfigured;
    }
}