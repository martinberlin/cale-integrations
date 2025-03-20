<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

//*
/**
 * @ORM\Entity(repositoryClass="App\Repository\ApiLogRepository")
 * @ORM\Table(name="app_apilog")
 * @ORM\HasLifecycleCallbacks
 */
class ApiLog
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="apiLogs")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="IntegrationApi", inversedBy="apiLogs")
     * @ORM\JoinColumn(name="api", referencedColumnName="uuid")
     */
    protected $api;

    /**
     * @ORM\Column(type="decimal", precision=2)
     */
    protected $temperature;

    /**
     * @ORM\Column(type="decimal", precision=2)
     */
    protected $humidity;

    /**
     * CO2 per ppm
     * @ORM\Column(type="integer")
     */
    protected $co2;

    /**
     * @ORM\Column(type="decimal", precision=2, nullable=true)
     */
    protected $light;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    protected $timezone;

    /**
     * now()
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    protected $datestamp;

    /**
     * now()
     * @ORM\Column(type="integer", options={"default": "CURRENT_TIMESTAMP"})
     */
    protected $timestamp;

    function __construct()
    {
        $this->co2 = 0;
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
    public function setCo2(int $value): void
    {
        $this->co2 = $value;
    }
    public function setTemperature(float $value): void
    {
        $this->temperature = $value;
    }
    public function setHumidity(float $value): void
    {
        $this->humidity = $value;
    }

    public function setTimezone(string $value): void
    {
        $this->timezone = $value;
        if ($value) {
            $this->datestamp->setTimezone(new \DateTimeZone($value));
            $this->datestamp = new \DateTime();
        }
    }
    public function setTimestamp(int $value): void
    {
        $this->timestamp = $value;
    }

    public function getDatestamp(): int
    {
        return $this->datestamp;
    }

    public function setDatestamp(int $value): void
    {
        $this->datestamp = date('Y-m-d H:i:s', $value);
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function getCo2(): int
    {
        return $this->co2;
    }
    public function getTemperature()
    {
        return $this->temperature;
    }
    public function getHumidity()
    {
        return $this->humidity;
    }
    public function getTimezone()
    {
        return $this->timezone;
    }
}