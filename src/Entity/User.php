<?php
namespace App\Entity;

use App\Entity\Model\Created;
use App\Entity\Model\Language;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="app_user",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="name_idx", columns={"name"}),@ORM\UniqueConstraint(name="apikey_idx", columns={"api_key"})})
 * @UniqueEntity("email")
 * @ORM\HasLifecycleCallbacks
 */
class User implements UserInterface, Language, Created
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * One user has many apis. This is the inverse side.
     * @ORM\OneToMany(targetEntity="UserApi", mappedBy="user", orphanRemoval=true)
     */
    private $userApis;

    /**
     * One user has many apis. This is the inverse side.
     * @ORM\OneToMany(targetEntity="UserApiGalleryImage", mappedBy="user", orphanRemoval=true)
     */
    private $userGalleryImages;

    /**
     * One user has many userFinanceCharts. This is the inverse side.
     * @ORM\OneToMany(targetEntity="UserApiFinancialChart", mappedBy="user", orphanRemoval=true)
     */
    private $userFinanceCharts;

    /**
     * One user has many wifi configurations. This is the inverse side.
     * @ORM\OneToMany(targetEntity="UserWifi", mappedBy="user", orphanRemoval=true)
     */
    private $userWifis;

    /**
     * One user has many screens. This is the inverse side.
     * @ORM\OneToMany(targetEntity="Screen", mappedBy="user", orphanRemoval=true)
     */
    private $screens;

    /**
     * One user has many shipped products. This is the inverse side.
     * @ORM\OneToMany(targetEntity="ShippingTracking", mappedBy="user", orphanRemoval=true)
     */
    private $userShippings;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=180, unique=true)
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     */
    protected $firstname;

    /**
     * @var string | Used in the route  website/{user.name}/my_screen
     *
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=100)
     */
    protected $password;

    /**
     * @var string|null
     */
    protected $plainPassword;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $passwordRequestToken;

    /**
     * @var string
     * @ORM\Column(type="string", length=2)
     */
    protected $language;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $paidLast;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    protected $paidTill;

    /**
     * @var integer | EURO
     * @ORM\Column(type="integer", options={"default" : 0})
     */
    protected $paidTotal;

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
    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $lastLogin;

    /**
     * @ORM\Column(type="string", nullable=true, length=60)
     */
    protected $timezone;

    /**
     * @ORM\Column(type="string", nullable=false, length=30)
     */
    protected $dateFormat;

    /**
     * @ORM\Column(type="string", nullable=false, length=12)
     */
    protected $hourFormat;

    /**
     * One user has many sysLog entries. This is the inverse side.
     * @ORM\OneToMany(targetEntity="SysLog", mappedBy="user", orphanRemoval=true)
     */
    private $sysLogs;

    /**
     * One user has many sysScreenLog entries. This is the inverse side.
     * @ORM\OneToMany(targetEntity="SysScreenLog", mappedBy="user", orphanRemoval=true)
     */
    private $sysScreenLogs;

    /**
     * @var string
     * @ORM\Column(type="boolean")
     */
    protected $agreementAccepted;

    /**
     * On true user won't receive any Email
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $doNotDisturb;

    /**
    * @ORM\Column(type="integer")
    */
    protected $maxScreens;

    /**
     * apiKey
     * @var string token
     * @ORM\Column(type="string", length=130, nullable=true)
     */
    protected $apiKey;

    /**
     * @var array $roles
     * @ORM\Column(type="array")
     */
    private $roles = [];

    public function __construct() {
        // Defaults
        $this->dateFormat = "D d.m.Y";
        $this->hourFormat = "H:i";
        $this->maxScreens = 3;
        $this->agreementAccepted = false;
        $this->userApis = new ArrayCollection();
        $this->userWifis = new ArrayCollection();
        $this->screens = new ArrayCollection();
        $this->sysLogs = new ArrayCollection();
        $this->sysScreenLogs = new ArrayCollection();
        $this->userShippings = new ArrayCollection();
        $this->userGalleryImages = new ArrayCollection();
        $this->userFinanceCharts = new ArrayCollection();
        $this->doNotDisturb = false;
        $this->setCreated(new \DateTime());
        $this->apiKey = strtoupper(hash("ripemd160", time()));
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
        $this->updated = $dateTime;
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

    public function getPaidLast() {
        return $this->paidLast;
    }
    public function setPaidLast(\DateTime $dateTime = null) {
        $this->paidLast = $dateTime;
    }

    public function getPaidTill() {
        return $this->paidTill;
    }
    public function setPaidTill(\DateTime $dateTime = null) {
        $this->paidTill = $dateTime;
    }

    public function getPaidTotal()
    {
        return $this->paidTotal;
    }
    public function setPaidTotal(int $paid) {
        $this->paidTotal = $paid;
    }

    /**
     * @param int $paid
     * SUMs paid to the total value in DB column
     */
    public function sumPaid(int $paid) {
        $this->paidTotal += $paid;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEmail():?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return User
     */
    public function setEmail(string $email): User
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getLanguage():?string
    {
        return $this->language;
    }

    /**
     * @param string $l
     *
     * @return User
     */
    public function setLanguage(string $l): User
    {
        $this->language = $l;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstname():?string
    {
        return $this->firstname;
    }

    /**
     * @param string $f
     *
     * @return User
     */
    public function setFirstname(string $f): User
    {
        $this->firstname = $f;
        return $this;
    }

    /**
     * @return string
     */
    public function getName():?string
    {
        return $this->name;
    }

    /**
     * @param string $u
     *
     * @return User
     */
    public function setName(string $u): User
    {
        $this->name = $u;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword():?string
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return User
     */
    public function setPassword(string $password): User
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @param null|string $plainPassword
     *
     * @return User
     */
    public function setPlainPassword(?string $plainPassword): User
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPasswordRequestToken(): ?string
    {
        return $this->passwordRequestToken;
    }

    /**
     * @param null|string $passwordRequestToken
     *
     * @return User
     */
    public function setPasswordRequestToken(?string $passwordRequestToken): User
    {
        $this->passwordRequestToken = $passwordRequestToken;

        return $this;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     *
     * @return User
     */
    public function setRoles(array $roles): User
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @return string The username
     */
    public function getUsername(): string
    {
        return $this->email;
    }

    /**
     * Removes sensitive data from the user.
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    /**
     * @return ArrayCollection
     */
    public function getUserApis() {
        return $this->userApis;
    }

    /**
     * @return ArrayCollection
     */
    public function getUserWifis() {
        return $this->userWifis;
    }

    /**
     * @return ArrayCollection
     */
    public function getScreens() {
        return $this->screens;
    }

    /**
     * @return \DateTime
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * @param \DateTime $lastLogin
     */
    public function setLastLogin(\DateTime $lastLogin = null)
    {
        $this->lastLogin = $lastLogin;
    }

    /**
     * @return bool
     */
    public function getAgreementAccepted()
    {
        return $this->agreementAccepted;
    }

    /**
     * @param bool $agreementAccepted
     */
    public function setAgreementAccepted(bool $agreementAccepted)
    {
        $this->agreementAccepted = $agreementAccepted;
    }

    /**
     * @return bool
     */
    public function getDoNotDisturb()
    {
        return $this->doNotDisturb;
    }

    /**
     * @param bool $agreementAccepted
     */
    public function setDoNotDisturb(bool $d)
    {
        $this->doNotDisturb = $d;
    }

    /**
     * @return mixed
     */
    public function getMaxScreens()
    {
        return $this->maxScreens;
    }

    /**
     * @param mixed $maxScreens
     */
    public function setMaxScreens(int $maxScreens)
    {
        $this->maxScreens = $maxScreens;
    }

    /**
     * @return mixed
     */
    public function getTimezone():?string
    {
        return $this->timezone;
    }

    /**
     * @param mixed $timezone
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * @return mixed
     */
    public function getDateFormat():?string
    {
        return $this->dateFormat;
    }

    /**
     * @param mixed $dateFormat
     */
    public function setDateFormat(string $dateFormat)
    {
        $this->dateFormat = $dateFormat;
    }

    /**
     * @return mixed
     */
    public function getHourFormat():?string
    {
        return $this->hourFormat;
    }

    /**
     * @param mixed $hourFormat
     */
    public function setHourFormat(string $hourFormat)
    {
        $this->hourFormat = $hourFormat;
    }

    /**
     * @return ArrayCollection
     */
    public function getSysLogs() {
        return $this->sysLogs;
    }

    /**
     * @return ArrayCollection
     */
    public function getSysScreenLogs() {
        return $this->sysScreenLogs;
    }

    /**
     * @return ArrayCollection
     */
    public function getUserShippings() {
        return $this->userShippings;
    }

    /**
     * @return ArrayCollection
     */
    public function getUserGalleryImages() {
        return $this->userGalleryImages;
    }

    /**
     * @return ArrayCollection
     */
    public function getUserFinanceCharts() {
        return $this->userFinanceCharts;
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param string
     */
    public function setApiKey($a)
    {
        $this->apiKey = $a;
    }

    public function getIdFirstname()
    {
        return (string) $this->id.'-'.$this->firstname;
    }

    public function __toString()
    {
        return (string) $this->name;
    }
}