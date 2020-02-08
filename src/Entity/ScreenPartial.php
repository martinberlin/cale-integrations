<?php
namespace App\Entity;

use App\Entity\Model\Sortable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ScreenContentRepository")
 * @ORM\Table(name="screen_partial")
 */
class ScreenPartial implements Sortable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Many partials have one screen. This is the owning side.
     * @ORM\ManyToOne(targetEntity="Screen", inversedBy="partials")
     * @ORM\JoinColumn(name="screen_id", referencedColumnName="uuid")
     */
    private $screen;

    /**
     * @ORM\ManyToOne(targetEntity="IntegrationApi")
     * @ORM\JoinColumn(name="screen_intapi_id", referencedColumnName="id")
     */
    protected $integrationApi;

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