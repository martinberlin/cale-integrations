<?php
namespace App\Entity;

use App\Entity\Model\Created;
use App\Entity\Model\Language;
use App\Entity\Model\Location;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\IntegrationApiRepository")
 * @ORM\Table(name="app_int_api",uniqueConstraints={@ORM\UniqueConstraint(name="user_api_name_idx", columns={"user_api_id", "name"})})
 */
class IntegrationApi implements Language, Location, Created
{
    /**
    * @ORM\Id
    * @ORM\Column(type="uuid", type="string", length=40, unique=true)
    */
    protected $uuid;

    /**
     * @ORM\ManyToOne(targetEntity="UserApi", inversedBy="integrationApis")
     * @ORM\JoinColumn(name="user_api_id", referencedColumnName="uuid")
     */
    protected $userApi;

    /**
     * @var string
     * @ORM\Column(type="string", length=130)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="latitude", type="decimal", precision=20, scale=16, nullable=true)
     */
    private $latitude;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude", type="decimal", precision=20, scale=16, nullable=true)
     */
    private $longitude;

    /**
     * @var string
     * @ORM\Column(type="string", length=2, nullable=true)
     */
    protected $language;

    /**
     * Placeholder for all additional settings
     * Skeleton forms starting from Api->getRequestParameters()
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $jsonSettings;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $created;

    function __construct()
    {
        $this->uuid = uniqid();
        $this->setCreated(new \DateTime());
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
    public function getUserApi()
    {
        return $this->userApi;
    }

    /**
     * @param mixed $userApi
     */
    public function setUserApi($userApi)
    {
        $this->userApi = $userApi;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getLatitude():?string
    {
        return $this->latitude;
    }

    /**
     * @param string $l
     * @return IntegrationApi
     */
    public function setLatitude(string $l)
    {
        $this->latitude = $l;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLongitude():?string
    {
        return $this->longitude;
    }

    /**
     * @param string $l
     * @return IntegrationApi
     */
    public function setLongitude(string $l)
    {
        $this->longitude = $l;
        return $this;
    }

    /**
     * @return string
     */
    public function getLanguage():?string
    {
        return $this->language;
    }

    /**
     * @param string $l
     *
     * @return User
     */
    public function setLanguage(string $l): IntegrationApi
    {
        $this->language = $l;
        return $this;
    }

    /**
     * @return string
     */
    public function getJsonSettings():?string
    {
        return $this->jsonSettings;
    }

    /**
     * @param string $jsonSettings
     */
    public function setJsonSettings(string $jsonSettings)
    {
        $this->jsonSettings = $jsonSettings;
    }
    public function getCreated() {
        return $this->created;
    }
    public function setCreated(\DateTime $dateTime = null) {
        $this->created = $dateTime;
    }

}