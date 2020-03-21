<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
/**
 * @ORM\Entity(repositoryClass="App\Repository\DisplayRepository")
 * @ORM\Table(name="display")
 * @UniqueEntity("className")
 */
class Display
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=40, unique=true)
     */
    protected $className;

    /**
     * @var string eink | tft
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    protected $type;

    /**
     * @var string
     * @ORM\Column(type="string", length=120, unique=true)
     */
    protected $name;
    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    protected $brand;

    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    protected $width;

    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    protected $height;

    /**
     * @var integer
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $grayLevels;

    /**
     * @var string
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    protected $activeSize;

    /**
     * @var integer
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $timeOfRefresh;

    /**
     * @var string
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    protected $manualUrl;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $purchaseUrl;

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
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getClassName():?string
    {
        return $this->className;
    }

    /**
     * @param string $className
     */
    public function setClassName(string $className)
    {
        $this->className = $className;
    }

    /**
     * @return string
     */
    public function getType():?string
    {
        return $this->type;
    }

    /**
     * @param string $className
     */
    public function setType(string $t)
    {
        $this->type = $t;
    }

    /**
     * @return string
     */
    public function getName():?string
    {
        return $this->name;
    }

    /**
     * @param string $className
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }
    /**
     * @return string
     */
    public function getBrand():?string
    {
        return $this->brand;
    }

    /**
     * @param string $brand
     */
    public function setBrand(string $brand)
    {
        $this->brand = $brand;
    }

    /**
     * @return int
     */
    public function getWidth():?int
    {
        return $this->width;
    }

    /**
     * @param int $width
     */
    public function setWidth(int $width)
    {
        $this->width = $width;
    }

    /**
     * @return int
     */
    public function getHeight():?int
    {
        return $this->height;
    }

    /**
     * @param int $height
     */
    public function setHeight(int $height)
    {
        $this->height = $height;
    }

    /**
     * @return int
     */
    public function getGrayLevels():?int
    {
        return $this->grayLevels;
    }

    /**
     * @param int $grayLevels
     */
    public function setGrayLevels(int $grayLevels)
    {
        $this->grayLevels = $grayLevels;
    }

    /**
     * @return string
     */
    public function getActiveSize():?string
    {
        return $this->activeSize;
    }

    /**
     * @param string $activeSize
     */
    public function setActiveSize(string $activeSize = null)
    {
        $this->activeSize = $activeSize;
    }

    /**
     * @return int
     */
    public function getTimeOfRefresh():?int
    {
        return $this->timeOfRefresh;
    }

    /**
     * @param int $timeOfRefresh
     */
    public function setTimeOfRefresh(int $timeOfRefresh)
    {
        $this->timeOfRefresh = $timeOfRefresh;
    }

    /**
     * @return string
     */
    public function getManualUrl():?string
    {
        return $this->manualUrl;
    }

    /**
     * @param string $manualUrl
     */
    public function setManualUrl(string $manualUrl)
    {
        $this->manualUrl = $manualUrl;
    }

    /**
     * @return string
     */
    public function getPurchaseUrl():?string
    {
        return $this->purchaseUrl;
    }

    /**
     * @param string $purchaseUrl
     */
    public function setPurchaseUrl(string $purchaseUrl)
    {
        $this->purchaseUrl = $purchaseUrl;
    }

    public function __toString()
    {
        return (string) $this->width." x ".$this->height.' '.$this->name;
    }
}