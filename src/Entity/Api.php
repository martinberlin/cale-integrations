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
     * @ORM\GeneratedValue(strategy="AUTO")
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
     * @ORM\Column(type="string", length=130)
     */
    protected $url;

    /**
     * @var string
     * @ORM\Column(type="string", length=40)
     */
    protected $responseType;
}