<?php
namespace App\Entity;

use App\Entity\Model\Sortable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TemplatePartialRepository")
 * @ORM\Table(name="template_partial")
 */
class TemplatePartial implements Sortable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;


    /**
     * @ORM\ManyToOne(targetEntity="IntegrationApi")
     * @ORM\JoinColumn(name="template_intapi_id", referencedColumnName="id")
     */
    protected $integrationApi;

    /**
     * Many template_partial have one template. This is the owning side.
     * @ORM\ManyToOne(targetEntity="Template", inversedBy="partials")
     * @ORM\JoinColumn(name="template_id", referencedColumnName="id")
     */
    protected $template;

    /**
     * @var string
     * @ORM\Column(type="string", length=130, nullable=false)
     */
    protected $placeholder;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $invertedColor;

    /**
     * @ORM\Column(type="integer")
     */
    protected $maxResults;

    /**
     * @ORM\Column(type="integer")
     */
    protected $sortPos;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Screen
     */
    public function setScreen(Screen $screen)
    {
        $this->screen = $screen;
    }

    /**
     * @return Screen
     */
    public function getScreen()
    {
        return $this->screen;
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
    public function getIntegrationApi()
    {
        return $this->integrationApi;
    }

    /**
     * @param mixed $integrationApi
     */
    public function setIntegrationApi($integrationApi)
    {
        $this->integrationApi = $integrationApi;
    }

    /**
     * @return mixed
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    /**
     * @param mixed $placeholder
     */
    public function setPlaceholder($placeholder)
    {
        $this->placeholder = $placeholder;
    }

    /**
     * @return mixed
     */
    public function getInvertedColor()
    {
        return $this->invertedColor;
    }

    /**
     * @param mixed $invertedColor
     */
    public function setInvertedColor($invertedColor)
    {
        $this->invertedColor = $invertedColor;
    }

    /**
     * @return mixed
     */
    public function getMaxResults()
    {
        return $this->maxResults;
    }

    /**
     * @param mixed $maxResults
     */
    public function setMaxResults($maxResults)
    {
        $this->maxResults = $maxResults;
    }

    /**
     * @return mixed
     */
    public function getSortPos()
    {
        return $this->sortPos;
    }

    /**
     * @param mixed $sortPos
     */
    public function setSortPos($sortPos)
    {
        $this->sortPos = $sortPos;
    }

}