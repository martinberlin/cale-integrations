<?php
namespace App\Entity;

use App\Entity\Model\Created;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * An User can have more than one package sent
 * @ORM\Entity(repositoryClass="App\Repository\ShippingTrackingRepository")
 * @ORM\Table(name="app_shipping",uniqueConstraints={@ORM\UniqueConstraint(name="shipping_idx", columns={"tracking"})})
 * @UniqueEntity("accessToken",message="This access token is already used")
 * @ORM\HasLifecycleCallbacks
 */
class ShippingTracking implements Created
{
    /**
     * The internal primary identity key.
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", type="string", length=40, unique=true)
     */
    protected $uuid;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userShippings")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @var string
     * in preparation | shipped | received | returns to sender
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    protected $status;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $tracking;

    /**
     * @var string
     * hermes | post.de
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    protected $sentBy;

    /**
     * @var string
     * ISO 3166-1 alpha-2 is the main set of two-letter country codes
     * @ORM\Column(type="string", length=2, nullable=true)
     */
    protected $countryCode;

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
        $this->status = 'in preparation';
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
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getTracking(): ?string
    {
        return $this->tracking;
    }

    /**
     * @param string $tracking
     */
    public function setTracking(?string $tracking): void
    {
        $this->tracking = $tracking;
    }

    /**
     * @return string
     */
    public function getSentBy(): ?string
    {
        return $this->sentBy;
    }

    /**
     * @param string $sentBy
     */
    public function setSentBy(?string $sentBy): void
    {
        $this->sentBy = $sentBy;
    }

    /**
     * @return string
     */
    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     */
    public function setCountryCode(?string $countryCode): void
    {
        $this->countryCode = $countryCode;
    }


}