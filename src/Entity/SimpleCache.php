<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity(repositoryClass="App\Repository\SimpleCacheRepository")
 * @ORM\Table(name="z_cache",uniqueConstraints={@ORM\UniqueConstraint(name="api_url_idx", columns={"int_api_id", "url"})})
 */
class SimpleCache
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=40)
     */
    protected $intApiId;

    /**
     * Hashed url. Hash type defined in cache_config
     * @ORM\Column(type="string", length=130)
     */
    protected $url;

    /**
     * @ORM\Column(name="created", type="integer", nullable=false)
     * @var integer
     */
    private $created;

    /**
     * @var string Long response from API stored here
     * @ORM\Column(type="text", nullable=true)
     */
    protected $responseContent;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $responseStatus;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $hits;

    public function __construct()
    {
        $this->created = time();
        $this->hits = 0;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getIntApiId()
    {
        return $this->intApiId;
    }

    /**
     * @param mixed $intApiId
     */
    public function setIntApiId($intApiId)
    {
        $this->intApiId = $intApiId;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getResponseContent():?string
    {
        return $this->responseContent;
    }

    /**
     * @param string $responseContent
     */
    public function setResponseContent(string $responseContent)
    {
        $this->responseContent = $responseContent;
    }

    /**
     * @return mixed
     */
    public function getResponseStatus()
    {
        return $this->responseStatus;
    }

    /**
     * @param mixed $responseStatus
     */
    public function setResponseStatus($responseStatus)
    {
        $this->responseStatus = $responseStatus;
    }

    /**
     * @return int
     */
    public function getCreated(): int
    {
        return $this->created;
    }

    /**
     * @param int $created
     */
    public function setCreated(int $created): void
    {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getHits()
    {
        return $this->hits;
    }

    /**
     * @param mixed $hits
     */
    public function setHits($hits): void
    {
        $this->hits = $hits;
    }

    /**
     * @param mixed $hits
     */
    public function incrHits()
    {
        $this->hits++;
    }
}