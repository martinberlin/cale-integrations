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
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Api")
     * @ORM\JoinColumn(name="api_id", referencedColumnName="id")
     */
    protected $api;

    /**
     * @var string
     * @ORM\Column(type="string", length=130)
     */
    protected $name;

    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    protected $maxResults;

    /**
     * @var string
     * @ORM\Column(type="string", length=60)
     */
    protected $orderBy;
}