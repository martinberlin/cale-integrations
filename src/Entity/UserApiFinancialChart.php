<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserApiFinancialChartRepository")
 * @ORM\Table(name="app_user_api_financial",uniqueConstraints={@ORM\UniqueConstraint(name="user_api_name_idx", columns={"user_id", "user_api_id"})})
 */
class UserApiFinancialChart
{
    /**
     * @ORM\id
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userFinanceCharts")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\id
     * @ORM\ManyToOne(targetEntity="IntegrationApi", inversedBy="financeCharts")
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
    protected $candleType = 'candlesticks';

    /**
     * @var string
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $symbol;

    /**
     * @var string
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $timeseries;

    /**
     * HEXA Color of the ascending candle
     *
     * @ORM\Column(type="string", length=7,nullable=true)
     */
    protected $colorAscending = 'black';

    /**
     * HEXA Color of the descending candle
     *
     * @ORM\Column(type="string", length=7,nullable=true)
     */
    protected $colorDescending = 'gray';

    /**
     * Font trueType file
     *
     * @ORM\Column(type="string", length=50,nullable=false)
     */
    protected $axisFontFile = 'digital-7.ttf';

    /**
     * @var integer
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $axisFontSize = 10;


    public function __toString()
    {
        return (string) $this->intApi.'_'.$this->symbol;
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

    /**
     * @return string
     */
    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    /**
     * @param string $symbol
     */
    public function setSymbol(string $symbol): void
    {
        $this->symbol = $symbol;
    }

    /**
     * @return string
     */
    public function getTimeseries(): ?string
    {
        return $this->timeseries;
    }

    /**
     * @param string $timeseries
     */
    public function setTimeseries(string $timeseries): void
    {
        $this->timeseries = $timeseries;
    }

    /**
     * @return string
     */
    public function getColorAscending(): string
    {
        return $this->colorAscending;
    }

    /**
     * @param string $colorAscending
     */
    public function setColorAscending(string $colorAscending): void
    {
        $this->colorAscending = $colorAscending;
    }

    /**
     * @return string
     */
    public function getColorDescending(): string
    {
        return $this->colorDescending;
    }

    /**
     * @param string $colorDescending
     */
    public function setColorDescending(string $colorDescending): void
    {
        $this->colorDescending = $colorDescending;
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
}