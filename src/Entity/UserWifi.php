<?php
namespace App\Entity;

use App\Entity\Model\Created;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * An User can implement the same api 2 times, but with different accessToken's
 * @ORM\Entity(repositoryClass="App\Repository\UserWifiRepository")
 * @ORM\Table(name="app_user_wifi")
 * @ORM\HasLifecycleCallbacks
 */
class UserWifi implements Created
{
    /**
     * The internal primary identity key.
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", type="string", length=40, unique=true)
     */
    protected $uuid;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userWifis")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @var string
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    protected $type;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $wifiSsid;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $wifiPass;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updated;

    function __construct()
    {
        $this->uuid = uniqid();
        $this->setCreated(new \DateTime());
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function updateModifiedDatetime() {
        // update the modified time
        $this->setUpdated(new \DateTime());
    }

    public function setUpdated(\DateTime $dateTime = null) {
        $this->uddated = $dateTime;
    }

    public function getUpdated() {
        return $this->updated;
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
     * @return string
     */
    public function getType():?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getWifiSsid():?string
    {
        return $this->wifiSsid;
    }

    /**
     * @param string $wifiSsid
     */
    public function setWifiSsid(string $wifiSsid)
    {
        $this->wifiSsid = $wifiSsid;
    }

    /**
     * @return string
     */
    public function getWifiPass():?string
    {
        return $this->wifiPass;
    }

    /**
     * @param string $wifiPass
     */
    public function setWifiPass($wifiPass)
    {
        $this->wifiPass = $wifiPass;
    }

    public function __toString()
    {
        return (string) $this->wifiSsid.'__'.$this->type;
    }
}