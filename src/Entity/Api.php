<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

//*
/**
 * @ORM\Entity(repositoryClass="App\Repository\ApiRepository")
 * @ORM\Table(name="app_api")
 * @UniqueEntity("urlName")
 * @ORM\HasLifecycleCallbacks
 */
class Api
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="ApiCategory")
     * @ORM\JoinColumn(name="api_cat_id", referencedColumnName="id")
     */
    protected $category;

    /**
     * @var string
     * @ORM\Column(type="string", length=30, unique=true)
     */
    protected $urlName;

    /**
     * @var string
     * @ORM\Column(type="string", length=130)
     */
    protected $name;

    /**
     * Short note explaining what key is needed. Ex. "Personal access token"
     * @var string
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    protected $authNote;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $url;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $requestParameters;

    /**
     * @var string
     * @ORM\Column(type="string", length=40)
     */
    protected $responseType;

    /**
     * @var string
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $isLocationApi;

    /**
     * @var string
     * @ORM\Column(type="string", length=240, nullable=true)
     */
    protected $documentationUrl;

    /**
     * Placeholder for all additional settings that are sent in the request. Advanced customization
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $defaultJsonSettings;

    /**
     * A route referring what method does the renderig to grab API contents
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    protected $jsonRoute;

    /**
     * One api has many userApis. This is the inverse side.
     * @ORM\OneToMany(targetEntity="UserApi", mappedBy="api")
     */
    private $userApis;

    /**
     * @var string
     * @ORM\Column(type="string", length=200, unique=true)
     */
    protected $editRoute;

    function __construct()
    {
        $this->isLocationApi = false;
        $this->userApis = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getDefaultJsonSettings():?string
    {
        return $this->defaultJsonSettings;
    }

    /**
     * @param string $jsonSettings
     */
    public function setDefaultJsonSettings(string $jsonSettings)
    {
        $this->defaultJsonSettings = $jsonSettings;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory(ApiCategory $category):void
    {
        $this->category = $category;
    }

    /**
     * @return string
     */
    public function getUrlName():?string
    {
        return $this->urlName;
    }

    /**
     * @param string $urlName
     * @return Api
     */
    public function setUrlName(string $urlName): Api
    {
        $this->urlName = $urlName;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Api
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getAuthNote()
    {
        return $this->authNote;
    }

    /**
     * @param string $authNote
     * @return Api
     */
    public function setAuthNote($authNote)
    {
        $this->authNote = $authNote;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return Api
     */
    public function setUrl(string $url= null)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getRequestParameters():?string
    {
        return $this->requestParameters;
    }

    /**
     * @param string JSON
     * @return Api
     */
    public function setRequestParameters(string $requestParameters): Api
    {
        $this->requestParameters = $requestParameters;
        return $this;
    }

    /**
     * @return string JSON
     */
    public function getResponseType():?string
    {
        return $this->responseType;
    }

    /**
     * @param string $responseType
     * @return Api
     */
    public function setResponseType(string $responseType): Api
    {
        $this->responseType = $responseType;
        return $this;
    }


    /**
     * @return boolean
     */
    public function isLocationApi():?bool
    {
        return $this->isLocationApi;
    }

    /**
     * @param boolean
     * @return Api
     */
    public function setIsLocationApi(bool $l): Api
    {
        $this->isLocationApi = $l;
        return $this;
    }

    /**
     * @return string
     */
    public function getDocumentationUrl()
    {
        return $this->documentationUrl;
    }

    /**
     * @param string $url
     * @return Api
     */
    public function setDocumentationUrl(string $url= null)
    {
        $this->documentationUrl = $url;
        return $this;
    }

    public function getJsonRoute()
    {
        return $this->jsonRoute;
    }

    /**
     * @param string $jsonRoute
     */
    public function setJsonRoute(string $jsonRoute)
    {
        $this->jsonRoute = $jsonRoute;
    }

    /**
     * @return ArrayCollection
     */
    public function getUserApis() {
        return $this->userApis;
    }


    /**
     * @return string
     */
    public function getEditRoute():?string
    {
        return $this->editRoute;
    }

    /**
     * @param string $editRoute
     */
    public function setEditRoute(string $editRoute)
    {
        $this->editRoute = $editRoute;
    }


    public function __toString()
    {
        return (string) $this->name;
    }

}