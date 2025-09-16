<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

//*
/**
 * @ORM\Entity(repositoryClass="App\Repository\ApiLogAmpereRepository")
 * @ORM\Table(name="app_apilog_ampere")
 * @ORM\HasLifecycleCallbacks
 */
class ApiLogAmpere
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="ApiLogAmpere")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="IntegrationApi", inversedBy="ApiLogAmpere")
     * @ORM\JoinColumn(name="api", referencedColumnName="uuid")
     */
    protected $api;

    /**
     * @ORM\Column(type="float")
     */
    protected $fp;

    /**
     * @ORM\Column(type="float")
     */
    protected $totalWh;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $volt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $watt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $hour;

    /**
     * now()
     * @ORM\Column(type="datetime")
     */
    protected $datestamp;

    /**
     * now()
     * @ORM\Column(type="integer")
     */
    protected $timestamp;

    /**
     * @ORM\Column(type="integer")
     */
    protected $timesRead = 0;

    private $timezone = 'Europe/Madrid';

    function __construct()
    {
        $this->timestamp = time();
        $this->datestamp = new \DateTime();
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
    public function setId($id): void
    {
        $this->id = $id;
    }

    public function setUser(User $id): void
    {
        $this->user = $id;
    }
    public function setApi(IntegrationApi $api): void
    {
        $this->api = $api;
    }
    public function setVolt(int $value): void
    {
        $this->volt = $value;
    }
    public function setFp(float $value): void
    {
        $this->fp = $value;
    }
    public function setTotalWh(float $value): void
    {
        $this->totalWh = $value;
    }
    public function setWatt(float $value): void
    {
        $this->watt = $value;
    }
    public function setHour(int $value): void
    {
        $this->hour = $value;
    }

    public function setTimezone(string $value): void
    {
        $this->timezone = $value;
        if ($value) {
            date_default_timezone_set($this->timezone);
        }
    }
    public function setTimestamp(int $value): void
    {
        $this->timestamp = $value;
    }

    public function getDatestamp() : \DateTime
    {
        return $this->datestamp;
    }

    public function setDatestamp(\DateTime $value): void
    {
        $this->datestamp->setTimezone(new \DateTimeZone($this->timezone));
        $this->datestamp = $value;
    }

    public function setTimesRead(int $value): void
    {
        $this->timesRead = $value;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function getVolt(): int
    {
        return $this->volt;
    }
    public function getFp()
    {
        return $this->fp;
    }
    public function getTotalWh()
    {
        return $this->totalWh;
    }
    // HR
    public function getWatt()
    {
        return $this->watt;
    }
    public function getHour()
    {
        return $this->hour;
    }
    public function getTimezone()
    {
        return $this->timezone;
    }

    public function getTimesRead() : int
    {
        return $this->timesRead;
    }

}