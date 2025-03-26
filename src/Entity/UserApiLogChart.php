<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserApiLogChartRepository")
 * @ORM\Table(name="app_user_api_log",uniqueConstraints={@ORM\UniqueConstraint(name="user_api_name_idx", columns={"user_id", "user_api_id"})})
 */
class UserApiLogChart
{
    /**
     * @ORM\id
     * @ORM\ManyToOne(targetEntity="User", inversedBy="UserApiLogChart")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\id
     * @ORM\ManyToOne(targetEntity="IntegrationApi", inversedBy="UserApiLogChart")
     * @ORM\JoinColumn(name="user_api_id", referencedColumnName="uuid")
     */
    protected $intApi;

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
    protected $co2ChartType = 'lines';

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
     * HEXA Color3
     * @ORM\Column(type="string", length=7,nullable=true)
     */
    protected $color3 = 'blue';

    /**
     * @ORM\Column(type="boolean", options={"default":"1"})
     */
    protected $exclude1 = true;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    protected $exclude2 = false;


    /**
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    protected $additionalChartCO2 = false;

    /**
     * @ORM\Column(type="boolean", options={"default":"1"})
     */
    protected $showXTickChart1 = true;

    /**
     * @ORM\Column(type="boolean", options={"default":"1"})
     */
    protected $showXTickChart2 = true;

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

    /**
     * @var string  TELEMETRYHARBOR
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $telemetryCargo;

    /**
    * @ORM\Column(type="string", length=50, nullable=true)
    */
    protected $telemetryDevice;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $telemetryApiKey;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    protected $telemetryIngestUrl;

    /**
     * @var string  TELEMETRYHARBOR 2ND Value
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $telemetryCargo2;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $telemetryDevice2;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $telemetryApiKey2;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    protected $telemetryIngestUrl2;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    protected $telemetryActive = false;

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

    public function getCo2ChartType(): ?string
    {
        return $this->co2ChartType;
    }

    /**
     * @param string $chart
     */
    public function setCo2ChartType(string $chart): void
    {
        $this->co2ChartType = $chart;
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

    public function getColor3(): ?string
    {
        return $this->color3;
    }
    public function setColor3(string $color): void
    {
        $this->color3 = $color;
    }

    public function getExclude1(): bool
    {
        return $this->exclude1;
    }
    public function setExclude1(bool $value): void
    {
        $this->exclude1 = $value;
    }

    public function getExclude2(): bool
    {
        return $this->exclude2;
    }
    public function setExclude2(bool $value): void
    {
        $this->exclude2 = $value;
    }


    public function getShowXTickChart1(): bool
    {
        return $this->showXTickChart1;
    }
    public function setShowXTickChart1(bool $value): void
    {
        $this->showXTickChart1 = $value;
    }

    public function getShowXTickChart2(): bool
    {
        return $this->showXTickChart2;
    }
    public function setShowXTickChart2(bool $value): void
    {
        $this->showXTickChart2 = $value;
    }

    public function getAdditionalChartCo2(): bool
    {
        return $this->additionalChartCO2;
    }
    public function setAdditionalChartCo2(bool $value): void
    {
        $this->additionalChartCO2 = $value;
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
    /** TELEMETRYHARBOR */
    public function setTelemetryActive(bool $v): void
    {
        $this->telemetryActive = $v;
    }

    public function getTelemetryActive(): bool
    {
        return $this->telemetryActive;
    }

    /**
     * @param string $v
     */
    public function setTelemetryApiKey(?string $v): void
    {
        $this->telemetryApiKey = $v;
    }

    public function getTelemetryApiKey(): ?string
    {
        return $this->telemetryApiKey;
    }

    /**
     * @param string $v
     */
    public function setTelemetryCargo(?string $v): void
    {
        $this->telemetryCargo = $v;
    }

    public function getTelemetryCargo(): ?string
    {
        return $this->telemetryCargo;
    }

    /**
     * @param string $v
     */
    public function setTelemetryDevice(?string $v): void
    {
        $this->telemetryDevice = $v;
    }

    public function getTelemetryDevice(): ?string
    {
        return $this->telemetryDevice;
    }

    public function setTelemetryIngestUrl(?string $v): void
    {
        $this->telemetryIngestUrl = $v;
    }

    public function getTelemetryIngestUrl(): ?string
    {
        return $this->telemetryIngestUrl;
    }
    //2nd CARGO
    public function setTelemetryApiKey2(?string $v): void
    {
        $this->telemetryApiKey2 = $v;
    }

    public function getTelemetryApiKey2(): ?string
    {
        return $this->telemetryApiKey2;
    }

    /**
     * @param string $v
     */
    public function setTelemetryCargo2(?string $v): void
    {
        $this->telemetryCargo2 = $v;
    }

    public function getTelemetryCargo2(): ?string
    {
        return $this->telemetryCargo2;
    }

    /**
     * @param string $v
     */
    public function setTelemetryDevice2(?string $v): void
    {
        $this->telemetryDevice2 = $v;
    }

    public function getTelemetryDevice2(): ?string
    {
        return $this->telemetryDevice2;
    }

    public function setTelemetryIngestUrl2(?string $v): void
    {
        $this->telemetryIngestUrl2 = $v;
    }

    public function getTelemetryIngestUrl2(): ?string
    {
        return $this->telemetryIngestUrl2;
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