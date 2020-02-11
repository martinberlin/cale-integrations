<?php
namespace App\Entity;

use App\Entity\Model\Created;
use App\Entity\Model\Language;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

//*
/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="app_user")
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
     * @ORM\OneToMany(targetEntity="UserApi", mappedBy="user")
     */
    private $userApis;

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
     * @ORM\Column(type="datetime")
     */
    protected $created;
    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updated;

    /**
     * @var array $roles
     *
     * @ORM\Column(type="array")
     */
    private $roles = [];

    public function __construct() {
        $this->userApis = new ArrayCollection();
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

    public function __toString()
    {
        return (string) $this->name;
    }
}