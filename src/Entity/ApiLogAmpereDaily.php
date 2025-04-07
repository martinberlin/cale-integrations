<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ApiLogAmpereDailyRepository")
 * @ORM\Table(name="app_apilog_ampere_daily")
 * @ORM\HasLifecycleCallbacks
 */
class ApiLogAmpereDaily
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
    protected $totalWh;

    /**
     * now()
     * @ORM\Column(type="datetime")
     */
    protected $datestamp;

    /**
     * @ORM\Column(type="integer")
     */
    private $hour;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $timezone;

    function __construct() {
        $this->datestamp = new \DateTime("Y-m-d");
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
    public function setHour(int $value): void
    {
        $this->hour = $value;
    }
    public function setTotalWh(float $value): void
    {
        $this->totalWh = $value;
    }

    public function getTimezone()
    {
        return $this->timezone;
    }
    public function setTimezone(string $value): void
    {
        $this->timezone = $value;
        if ($value) {
            date_default_timezone_set($this->timezone);
        }
    }

    public function getDatestamp() : \DateTime
    {
        return $this->datestamp;
    }

    public function setDatestamp(\DateTime $value): void
    {
        $this->datestamp->setTimezone(new \DateTimeZone($this->timezone));
        $this->datestamp = new \DateTime('Y-m-d');
    }
    public function getTotalWh()
    {
        return $this->totalWh;
    }
    public function getHour()
    {
        return $this->hour;
    }


}