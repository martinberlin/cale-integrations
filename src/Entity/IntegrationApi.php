<?php
namespace App\Entity;

use App\Entity\Model\Created;
use App\Entity\Model\Language;
use App\Entity\Model\Location;
use Doctrine\Common\Collections\ArrayCollection;
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
     * One IntegrationApi has many partials. This is the inverse side.
     * @ORM\OneToMany(targetEntity="TemplatePartial", mappedBy="integrationApi",orphanRemoval=true)
     */
    private $partials;

    /**
     * One IntegrationApi may haves many galleryImages. This is the inverse side.
     * @ORM\OneToMany(targetEntity="UserApiGalleryImage", mappedBy="intApi", orphanRemoval=true)
     */
    private $galleryImages;

    /**
     * @var string
     * @ORM\Column(type="string", length=130)
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=200,nullable=true)
     */
    protected $calId;

    /**
     * @var string
     * @ORM\Column(type="string", length=40,nullable=true)
     */
    protected $timezone;

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
     * @ORM\Column(type="text", length=1000, nullable=true)
     */
    protected $jsonSettings;

    /**
     * Internal content: Used for internal Html storage
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $html;

    /**
     * Internal content: Image path
     * @var string
     * @ORM\Column(type="string", length=120, nullable=true)
     */
    protected $imagePath;

    /**
     * Internal content: Image alignment
     * @var string
     * @ORM\Column(type="string", length=120, nullable=true, name="image_pos")
     */
    protected $imagePosition;

    /**
     * Internal content: Image type (Floating or background)
     * @var string
     * @ORM\Column(type="string", length=20, nullable=true, name="image_type")
     */
    protected $imageType;

    /**
     * Generic units like used in weather for Metric & Imperial
     * @var string
     * @ORM\Column(type="string", length=20, nullable=true, name="units")
     */
    protected $units;

    /**
     * @var integer
     * @ORM\Column(type="integer",nullable=true)
     */
    protected $galleryIndex;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $created;

    function __construct()
    {
        $this->uuid = uniqid();
        $this->setCreated(new \DateTime());
        $this->galleryImages = new ArrayCollection();
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
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getCalId()
    {
        return $this->calId;
    }

    /**
     * @param mixed $name
     */
    public function setCalId(string $name)
    {
        $this->calId = $name;
    }

    /**
     * @return mixed
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @param mixed $name
     */
    public function setTimezone(string $name)
    {
        $this->timezone = $name;
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
    public function setJsonSettings(string $jsonSettings=null)
    {
        $this->jsonSettings = $jsonSettings;
    }

    /**
     * @return string
     */
    public function getHtml():?string
    {
        return $this->html;
    }

    /**
     * @param string $html
     */
    public function setHtml($html): void
    {
        $this->html = $html;
    }

    /**
     * @return string
     */
    public function getImagePath():?string
    {
        return $this->imagePath;
    }

    public function setImagePath(string $imagePath): void
    {
        $this->imagePath = $imagePath;
    }

    /**
     * @return string
     */
    public function getImagePosition():?string
    {
        return $this->imagePosition;
    }

    /**
     * @param string
     */
    public function setImagePosition(string $imagePos): void
    {
        $this->imagePosition = $imagePos;
    }

    /**
     * @return string
     */
    public function getImageType():?string
    {
        return $this->imageType;
    }

    /**
     * @param string
     */
    public function setImageType(string $imageType): void
    {
        $this->imageType = $imageType;
    }

    /**
     * @return string
     */
    public function getUnits():?string
    {
        return $this->units;
    }

    /**
     * @param string
     */
    public function setUnits(string $u): void
    {
        $this->units = $u;
    }

    /**
     * @return ArrayCollection
     */
    public function getGalleryImages()
    {
        return $this->galleryImages;
    }

    /**
     * @return int
     */
    public function getGalleryIndex(): int
    {
        return $this->galleryIndex;
    }

    /**
     * @param int $galleryIndex
     */
    public function setGalleryIndex(int $galleryIndex): void
    {
        $this->galleryIndex = $galleryIndex;
    }


    public function getCreated() {
        return $this->created;
    }
    public function setCreated(\DateTime $dateTime = null) {
        $this->created = $dateTime;
    }

    public function __toString()
    {
        return (string) $this->name;
    }

}