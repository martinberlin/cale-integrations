<?php
namespace App\Entity;

use App\Entity\Model\Created;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ScreenRepository")
 * @ORM\Table(name="screen")
 * @UniqueEntity("uuid")
 * @ORM\HasLifecycleCallbacks
 */
class Screen implements Created
{
    /**
     * The internal primary identity key.
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", type="string", length=40, unique=true)
     */
    protected $uuid;

    /**
     * @var string
     * @ORM\Column(type="string", length=130, nullable=true)
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Display")
     * @ORM\JoinColumn(name="display_id", referencedColumnName="id")
     */
    protected $display;

    /**
     * One Template has many partials. This is the inverse side.
     * @ORM\OneToMany(targetEntity="TemplatePartial", mappedBy="screen")
     */
    private $partials;

    /**
     * @var string
     * @ORM\Column(type="string", length=130, nullable=true)
     */
    protected $templateTwig;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $created;

    function __construct()
    {
        $this->uuid = uniqid();
        $this->partials = new ArrayCollection();
        $this->setCreated(new \DateTime());
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
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param mixed $uuid
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
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
     * @return mixed
     */
    public function getDisplay()
    {
        return $this->display;
    }

    /**
     * @param mixed $display
     */
    public function setDisplay($display)
    {
        $this->display = $display;
    }

    /**
     * @return string
     */
    public function getName():?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }


    /**
     * @return string
     */
    public function getTemplateTwig():?string
    {
        return $this->templateTwig;
    }

    /**
     * @param string $templateTwig
     */
    public function setTemplateTwig(string $templateTwig)
    {
        $this->templateTwig = $templateTwig;
    }

    public function addPartial(ScreenPartial $partial): self
    {
        if (!$this->partials->contains($partial)) {
            $this->partials[] = $partial;
        }
        return $this;
    }

    public function removePartial(ScreenPartial $partial): self
    {
        if ($this->partials->contains($partial)) {
            $this->partials->removeElement($partial);
        }
    }

    /**
     * @return mixed
     */
    public function getPartials()
    {
        return $this->partials;
    }

    /**
     * @param mixed $partials
     */
    public function setPartials($partials)
    {
        $this->partials = $partials;
    }
}