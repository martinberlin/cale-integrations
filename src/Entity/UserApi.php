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
     * @ORM\ManyToOne(targetEntity="Api", inversedBy="userApis")
     * @ORM\JoinColumn(name="api_id", referencedColumnName="id")
     */
    protected $api;

    /**
     * One userApi has many integrations. This is the inverse side.
     * @ORM\OneToMany(targetEntity="IntegrationApi", mappedBy="userApi", orphanRemoval=true)
     */
    private $integrationApis;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $resourceUrl;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $username;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $password;

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
     * @var string
     * @ORM\Column(type="string", length=250, nullable=true)
     */
    protected $scope;

    /**
     * @var string
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    protected $region;

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
        $this->updated = $dateTime;
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

    /**
     * @param string
     */
    public function setScope(string $s)
    {
        $this->scope = $s;
    }

    /**
     * @return string
     */
    public function getScope():?string
    {
        return $this->scope;
    }

    /**
     * @return string
     */
    public function getResourceUrl():?string
    {
        return $this->resourceUrl;
    }

    /**
     * @param string $resourceUrl
     */
    public function setResourceUrl(string $resourceUrl): void
    {
        $this->resourceUrl = $resourceUrl;
    }

    /**
     * @return string
     */
    public function getUsername():?string
    {
        return $this->username;
    }

    /**
     * @param string
     */
    public function setUsername(string $u)
    {
        $this->username = $u;
    }

    /**
     * @return string
     */
    public function getPassword():?string
    {
        $decrypt = openssl_decrypt($this->password, $_ENV['ENCRYPT_ALGO'], $this->uuid);
        return $decrypt;
    }


    /**
     * @param string
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function encryptPassword()
    {
        if (isset($this->password)) {
        $encrypt = openssl_encrypt($this->password, $_ENV['ENCRYPT_ALGO'], $this->uuid);
        $this->password = $encrypt;
        }
    }

    /**
     * @param string
     */
    public function setPassword(?string $pass)
    {
        if ($pass) {
         $this->password = $pass;
        }
    }

    /**
     * @return string
     */
    public function getRegion():?string
    {
        return $this->region;
    }

    /**
     * @param string
     */
    public function setRegion(string $r)
    {
        $this->region = $r;
    }

}