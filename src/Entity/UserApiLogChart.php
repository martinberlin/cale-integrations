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
    protected $candleType = 'candlesticks';

    /**
     * HEXA Color of the ascending candle
     *
     * @ORM\Column(type="string", length=7,nullable=true)
     */
    protected $color= 'black';
    protected $color2= 'white';
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

    /**
     * @return string
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * @param string $color
     */
    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    public function getColor2(): string
    {
        return $this->color2;
    }
    public function setColor2(string $color): void
    {
        $this->color2 = $color;
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