<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

//*
/**
 * @ORM\Entity(repositoryClass="App\Repository\IntegrationApiRepository")
 * @ORM\Table(name="app_int_api")
 * @UniqueEntity("urlName")
 * @ORM\HasLifecycleCallbacks
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

}