<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserApiGalleryImageRepository")
 * @ORM\Table(name="app_user_api_gallery",uniqueConstraints={@ORM\UniqueConstraint(name="user_api_name_idx", columns={"user_id", "user_api_id", "image_id"})})
 */
class UserApiGalleryImage
{
    /**
     * @ORM\id
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userGalleryImages")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\id
     * @ORM\ManyToOne(targetEntity="IntegrationApi", inversedBy="galleryImages")
     * @ORM\JoinColumn(name="user_api_id", referencedColumnName="uuid")
     */
    protected $intApi;

    /**
     * @ORM\id
     * @var integer
     * @ORM\Column(type="integer",nullable=false)
     */
    protected $imageId;

    /**
     * @var string
     * @ORM\Column(type="string", length=4, nullable=false)
     */
    protected $extension;

    /**
     * @var integer
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $kb = 0;

    /**
     * @var string
     * @ORM\Column(type="string", length=140, nullable=true)
     */
    protected $caption;

    /**
     * @var integer
     * @ORM\Column(type="integer",nullable=false)
     */
    protected $position = 1;

    public function __toString()
    {
        return (string) $this->intApi.'_'.$this->imageId;
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
    public function getImageId(): int
    {
        return $this->imageId;
    }

    /**
     * @param int $imageId
     */
    public function setImageId(int $imageId): void
    {
        $this->imageId = $imageId;
    }

    /**
     * @return string
     */
    public function getCaption(): string
    {
        return $this->caption;
    }

    /**
     * @param string $caption
     */
    public function setCaption($caption)
    {
        $this->caption = $caption;
    }

    /**
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * @param string $extension
     */
    public function setExtension(string $extension): void
    {
        $this->extension = $extension;
    }

    /**
     * @return int
     */
    public function getKb(): int
    {
        return $this->kb;
    }

    /**
     * @param int $kb
     */
    public function setKb(int $kb): void
    {
        $this->kb = $kb;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }
}