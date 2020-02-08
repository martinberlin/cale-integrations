<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\IntegrationApiRepository")
 * @ORM\Table(name="app_int_api")
 */
class IntegrationApi
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="UserApi")
     * @ORM\JoinColumn(name="user_api_id", referencedColumnName="id")
     */
    protected $userApi;

    /**
     * @var string
     * @ORM\Column(type="string", length=130)
     */
    protected $name;

    /**
     * Skeleton forms starting from Api->getRequestParameters()
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $jsonSettings;

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
    public function getUserApi()
    {
        return $this->userApi;
    }

    /**
     * @param mixed $userApi
     */
    public function setUserApi($userApi)
    {
        $this->userApi = $userApi;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getJsonSettings(): string
    {
        return $this->jsonSettings;
    }

    /**
     * @param string $jsonSettings
     */
    public function setJsonSettings(string $jsonSettings)
    {
        $this->jsonSettings = $jsonSettings;
    }

}