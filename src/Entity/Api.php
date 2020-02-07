<?php
namespace App\Entity;

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
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Api
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrlName(): string
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
    public function getRequestParameters(): string
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
    public function getResponseType(): string
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

}