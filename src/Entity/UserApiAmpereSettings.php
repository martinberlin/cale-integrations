<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserApiAmpereSettingsRepository")
 * @ORM\Table(name="app_user_ampere_settings",uniqueConstraints={@ORM\UniqueConstraint(name="user_api_name_idx", columns={"user_id", "user_api_id"})})
 */
class UserApiAmpereSettings
{
    /**
     * @ORM\id
     * @ORM\ManyToOne(targetEntity="User", inversedBy="UserApiAmpereSettings")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\id
     * @ORM\ManyToOne(targetEntity="IntegrationApi", inversedBy="UserApiAmpereSettings")
     * @ORM\JoinColumn(name="user_api_id", referencedColumnName="uuid")
     */
    protected $intApi;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $resetCounterDay = 1;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $costKilowattHour = 0.13;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $width = 400;

    /**
     * @var integer
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $height = 300;

    /**
     * @var integer
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $dataRows = 31;

    /**
     * @var string
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $candleType = 'bars';

    /**
     * @var string
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $candleType2 = 'lines';

    /**
     * @var string
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    protected $timezone = 'Europe/Madrid';

    /**
     * @var \DateTime Represents the last reset date of energy consumption (resetCounterDay)
     * @ORM\Column(type="datetime",nullable=true)
     */
    protected $datetimeLastReset = null;

    /**
     * HEXA Color1
     * @ORM\Column(type="string", length=7,nullable=true)
     */
    protected $color1 = 'black';

    /**
     * HEXA Color2
     * @ORM\Column(type="string", length=7,nullable=true)
     */
    protected $color2 = 'green';

    /**
     * @ORM\Column(type="boolean", options={"default":"1"})
     */
    protected $exclude1 = true;


    /**
     * Font trueType file
     *
     * @ORM\Column(type="string", length=50,nullable=true)
     */
    protected $axisFontFile = 'digital-7.ttf';

    /**
     * @var integer
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $axisFontSize = 10;

    public function __toString()
    {
        return (string) $this->intApi->getName();
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
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getIntApi()
    {
        return $this->intApi;
    }

    /**
     * @param mixed $intApi
     */
    public function setIntApi($intApi): void
    {
        $this->intApi = $intApi;
    }

    public function setDatestampLastReset(\DateTime $value): void
    {
        if ($this->datetimeLastReset instanceof \DateTime  == false) {
            $this->datetimeLastReset = new \DateTime();
        }
        $this->datetimeLastReset->setTimezone(new \DateTimeZone($this->timezone));
        $this->datetimeLastReset = $value;
    }
    public function getDatestampLastReset(): ?\DateTime
    {
        return $this->datetimeLastReset;
    }

    public function getCostKilowattHour(): ?float
    {
        return $this->costKilowattHour;
    }
    public function setCostKilowattHour(?float $value): void
    {
        $this->costKilowattHour = $value;
    }

    public function getResetCounterDay(): int
    {
        return $this->resetCounterDay;
    }

    public function setResetCounterDay(int $resetCounterDay): void
    {
        $this->resetCounterDay = $resetCounterDay;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @param int $width
     */
    public function setWidth(int $width): void
    {
        $this->width = $width;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @param int $height
     */
    public function setHeight(int $height): void
    {
        $this->height = $height;
    }

    /**
     * @return string
     */
    public function getCandleType(): ?string
    {
        return $this->candleType;
    }

    /**
     * @param string $candleType
     */
    public function setCandleType(string $candleType): void
    {
        $this->candleType = $candleType;
    }

    public function getCandleType2(): ?string
    {
        return $this->candleType2;
    }

    /**
     * @param string $chart
     */
    public function setCandleType2(string $chart): void
    {
        $this->candleType2 = $chart;
    }


    /**
     * @return string
     */
    public function getColor1(): ?string
    {
        return $this->color1;
    }

    /**
     * @param string $color
     */
    public function setColor1(string $color): void
    {
        $this->color1 = $color;
    }

    public function getColor2(): ?string
    {
        return $this->color2;
    }
    public function setColor2(string $color): void
    {
        $this->color2 = $color;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }
    public function setTimezone(string $t): void
    {
        $this->timezone = $t;
    }

    public function getExclude1(): bool
    {
        return $this->exclude1;
    }
    public function setExclude1(bool $value): void
    {
        $this->exclude1 = $value;
    }

    /**
     * @return string
     */
    public function getAxisFontFile(): ?string
    {
        return $this->axisFontFile;
    }

    /**
     * @param string $axisFontFile
     */
    public function setAxisFontFile(string $axisFontFile): void
    {
        $this->axisFontFile = $axisFontFile;
    }

    /**
     * @return int
     */
    public function getAxisFontSize(): int
    {
        return $this->axisFontSize;
    }

    /**
     * @param int $axisFontSize
     */
    public function setAxisFontSize(int $axisFontSize): void
    {
        $this->axisFontSize = $axisFontSize;
    }

    /**
     * @return int
     */
    public function getDataRows(): int
    {
        return $this->dataRows;
    }

    /**
     * @param int $dataRows
     */
    public function setDataRows(int $dataRows): void
    {
        $this->dataRows = $dataRows;
    }

    // Dummy
    public function getJsonSettings():?string
    {
        return '';
    }

    // Dummy
    public function setJsonSettings(string $jsonSettings=null)
    {
    }
}