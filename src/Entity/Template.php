<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TemplateRepository")
 * @ORM\Table(name="screen_template")
 */
class Template

{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * One Template has many Screens. This is the inverse side.
     * @ORM\OneToMany(targetEntity="Screen", mappedBy="template")
     */
    private $screens;

    /**
     * One Template has many partials. This is the inverse side.
     * @ORM\OneToMany(targetEntity="TemplatePartial", mappedBy="template")
     */
    private $partials;

    /**
     * @var string
     * @ORM\Column(type="string", length=130, nullable=true)
     */
    protected $templateTwig;

    public function __construct()
    {
        $this->screens = new ArrayCollection();
    }

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
     * @return mixed
     */
    public function getScreens()
    {
        return $this->screens;
    }

    /**
     * @param mixed $screen
     */
    public function setScreens($screen)
    {
        $this->screens = $screen;
    }

    /**
     * @return string
     */
    public function getTemplateTwig(): string
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