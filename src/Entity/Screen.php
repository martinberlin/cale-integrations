<?php
namespace App\Entity;

use App\Entity\Model\Created;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ScreenRepository")
 * @ORM\Table(name="screen")
 * @UniqueEntity("uuid")
 * @ORM\HasLifecycleCallbacks
 */
class Screen implements Created
{
    /**
     * The internal primary identity key.
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", type="string", length=40, unique=true)
     */
    protected $uuid;

    /**
     * @var string
     * @ORM\Column(type="string", length=130, nullable=true)
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="screens")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Display")
     * @ORM\JoinColumn(name="display_id", referencedColumnName="id")
     */
    protected $display;

    /**
     * One screen has many partials. This is the inverse side.
     * @ORM\OneToMany(targetEntity="TemplatePartial", mappedBy="screen",cascade={"persist"},orphanRemoval=true)
     */
    private $partials;

    /**
     * One screen has many sysScreenLog entries. This is the inverse side.
     * @ORM\OneToMany(targetEntity="SysScreenLog", mappedBy="screen", orphanRemoval=true)
     */
    private $sysScreenLogs;

    /**
     * @var string
     * @ORM\Column(type="string", length=130, nullable=true)
     */
    protected $templateTwig;

    /**
     * Output settings
     * @var string Bearer token
     * @ORM\Column(type="string", length=130, nullable=true)
     */
    protected $outBearer;

    /**
     * @var string
     * @ORM\Column(name="zoom", type="decimal", precision=4, scale=2, nullable=true)
     */
    private $outZoomFactor;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $outBrightness;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $outBitDepth;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $outCacheSeconds;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $outCompressed;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $loggingActive;

    /**
     * On false an Authorization Bearer should be sent
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $public;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $outSsl;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $imgDither;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $imgDitherOptions;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $hits;

    /**
     * @ORM\Column(type="integer", name="std_from", nullable=true)
     */
    protected $stDayFrom;
    /**
     * @ORM\Column(type="integer", name="std_to", nullable=true)
     */
    protected $stDayTo;
    /**
     * @ORM\Column(type="integer", name="sth_from", nullable=true)
     */
    protected $stHourFrom;
    /**
     * @ORM\Column(type="integer", name="sth_to", nullable=true)
     */
    protected $stHourTo;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $created;

    function __construct()
    {
        $this->uuid = uniqid();
        $this->outBearer = hash("sha256", $this->uuid);
        $this->public = 1;
        $this->outBrightness = 100;
        $this->outZoomFactor = 0.8;
        $this->outCacheSeconds = 30;
        $this->outBitDepth = 1;
        $this->outCompressed = 0;
        $this->loggingActive = true;
        $this->stDayTo = '7';
        $this->stHourTo = '23';
        $this->outSsl = 0;
        $this->imgDither = true;
        $this->hits = 0;
        $this->partials = new ArrayCollection();
        $this->sysScreenLogs = new ArrayCollection();
        $this->setCreated(new \DateTime());
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
    public function getDisplay()
    {
        return $this->display;
    }

    /**
     * @param mixed $display
     */
    public function setDisplay($display)
    {
        $this->display = $display;
    }

    /**
     * @return string
     */
    public function getName():?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }


    /**
     * @return string
     */
    public function getTemplateTwig():?string
    {
        return $this->templateTwig;
    }

    /**
     * @param string $templateTwig
     */
    public function setTemplateTwig(string $templateTwig)
    {
        $this->templateTwig = $templateTwig;
    }

    public function addPartial(TemplatePartial $partial): self
    {
        if (!$this->partials->contains($partial)) {
            $this->partials[] = $partial;
        }
        return $this;
    }

    public function removePartial(TemplatePartial $partial): self
    {
        if ($this->partials->contains($partial)) {
            $this->partials->removeElement($partial);
        }
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getPartials()
    {
        return $this->partials;
    }

    /**
     * @return ArrayCollection
     */
    public function getSysScreenLogs()
    {
        return $this->sysScreenLogs;
    }

    /**
     * @param mixed $partials
     */
    public function setPartials($partials)
    {
        $this->partials = $partials;
    }

    /**
     * @return mixed
     */
    public function getHits()
    {
        return $this->hits;
    }

    /**
     * @param mixed $hits
     */
    public function setHits($hits): void
    {
        $this->hits = $hits;
    }

    /**
     * @param mixed $hits
     */
    public function incrHits()
    {
        $this->hits++;
    }


    /**
     * @return mixed
     */
    public function getOutBearer()
    {
        return $this->outBearer;
    }

    /**
     * @param mixed $outBearer
     */
    public function setOutBearer($outBearer)
    {
        $this->outBearer = $outBearer;
    }

    /**
     * @return string
     */
    public function getOutZoomFactor():?string
    {
        return $this->outZoomFactor;
    }

    /**
     * @param string $outZoomFactor
     */
    public function setOutZoomFactor(string $outZoomFactor)
    {
        $this->outZoomFactor = $outZoomFactor;
    }

    /**
     * @return mixed
     */
    public function getOutBrightness()
    {
        return $this->outBrightness;
    }

    /**
     * @param mixed $outBrightness
     */
    public function setOutBrightness($outBrightness)
    {
        $this->outBrightness = $outBrightness;
    }

    /**
     * @return mixed
     */
    public function getOutBitDepth()
    {
        return $this->outBitDepth;
    }

    /**
     * @param mixed $outBitDepth
     */
    public function setOutBitDepth($outBitDepth)
    {
        $this->outBitDepth = $outBitDepth;
    }

    /**
     * @return mixed
     */
    public function getOutCacheSeconds()
    {
        return $this->outCacheSeconds;
    }

    /**
     * @param mixed $outCacheSeconds
     */
    public function setOutCacheSeconds($outCacheSeconds)
    {
        $this->outCacheSeconds = $outCacheSeconds;
    }

    /**
     * @return mixed
     */
    public function getOutCompressed()
    {
        return $this->outCompressed;
    }

    /**
     * @param mixed $outCompressed
     */
    public function setOutCompressed($outCompressed)
    {
        $this->outCompressed = $outCompressed;
    }

    /**
     * @return bool
     */
    public function getLoggingActive()
    {
        return $this->loggingActive;
    }

    /**
     * @param bool
     */
    public function setLoggingActive($l)
    {
        $this->loggingActive = $l;
    }

    /**
     * @return boolean
     */
    public function isPublic(): bool
    {
        return $this->public;
    }

    /**
     * @param boolean $isPublic
     */
    public function setPublic(bool $isPublic)
    {
        $this->public = $isPublic;
    }

    /**
     * @return boolean
     */
    public function isOutSsl(): bool
    {
        return $this->outSsl;
    }

    /**
     * @param boolean $ssl
     */
    public function setOutSsl(bool $ssl)
    {
        $this->outSsl = $ssl;
    }

    /**
     * @return mixed
     */
    public function getStDayFrom()
    {
        return $this->stDayFrom;
    }

    /**
     * @param mixed $stDayFrom
     */
    public function setStDayFrom($stDayFrom)
    {
        $this->stDayFrom = $stDayFrom;
    }

    /**
     * @return mixed
     */
    public function getStDayTo()
    {
        return $this->stDayTo;
    }

    /**
     * @param mixed $stDayTo
     */
    public function setStDayTo($stDayTo)
    {
        $this->stDayTo = $stDayTo;
    }

    /**
     * @return mixed
     */
    public function getStHourFrom()
    {
        return $this->stHourFrom;
    }

    /**
     * @param mixed $stHourFrom
     */
    public function setStHourFrom($stHourFrom)
    {
        $this->stHourFrom = $stHourFrom;
    }

    /**
     * @return mixed
     */
    public function getStHourTo()
    {
        return $this->stHourTo;
    }

    /**
     * @param mixed $stHourTo
     */
    public function setStHourTo($stHourTo)
    {
        $this->stHourTo = $stHourTo;
    }

    public function getImgDither()
    {
        return $this->imgDither;
    }

    /**
     * @param bool $imgDither
     */
    public function setImgDither(bool $imgDither)
    {
        $this->imgDither = $imgDither;
    }

    public function getImgDitherOptions()
    {
        return $this->imgDitherOptions;
    }

    /**
     * @param string $options
     */
    public function setImgDitherOptions($options)
    {
        $this->imgDitherOptions = $options;
    }

    public function __toString()
    {
        return (string) "Screen: ".$this->uuid." Name: ".$this->name;
    }
}