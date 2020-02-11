<?php
namespace App\Entity;

use App\Entity\Model\Created;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * An User can implement the same api 2 times, but with different accessToken's
 * @ORM\Entity(repositoryClass="App\Repository\UserApiRepository")
 * @ORM\Table(name="app_user_api",uniqueConstraints={@ORM\UniqueConstraint(name="token_idx", columns={"access_token", "user_id"})})
 * @UniqueEntity("accessToken",message="This access token is already used")
 * @ORM\HasLifecycleCallbacks
 */
class UserApi implements Created
{
    /**
     * The internal primary identity key.
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", type="string", length=40, unique=true)
     */
    protected $uuid;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userApis")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Api")
     * @ORM\JoinColumn(name="api_id", referencedColumnName="id")
     */
    protected $api;

    /**
     * One userApi has many integrations. This is the inverse side.
     * @ORM\OneToMany(targetEntity="IntegrationApi", mappedBy="userApi")
     */
    private $integrationApis;

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
     * Used for Google
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $jsonToken;

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
        $this->uuid = uniqid();
        $this->integrationApis = new ArrayCollection();
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
        return $this->uuid;
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
    public function getCredentials():?string
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
    public function getJsonToken():?string
    {
        return $this->jsonToken;
    }

    /**
     * @param string $jsonToken
     */
    public function setJsonToken(string $jsonToken): void
    {
        $this->jsonToken = $jsonToken;
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
    public function setIsConfigured(bool $isConfigured)
    {
        $this->isConfigured = $isConfigured;
    }

    /**
     * @return ArrayCollection
     */
    public function getIntegrationApis()
    {
        return $this->integrationApis;
    }

}