<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ScreenRepository")
 * @ORM\Table(name="screen")
 * @UniqueEntity("uuid")
 * @ORM\HasLifecycleCallbacks
 */
class Screen
{
    /**
     * The internal primary identity key.
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", type="string", unique=true)
     */
    protected $uuid;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Display")
     * @ORM\JoinColumn(name="display_id", referencedColumnName="id")
     */
    protected $display;

    /**
     * @var string
     * @ORM\Column(type="string", length=130, nullable=true)
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=130, nullable=true)
     */
    protected $template;

    /**
     * One Screen has many partials. This is the inverse side.
     * @ORM\OneToMany(targetEntity="ScreenPartial", mappedBy="screen")
     */
    private $partials;

    function __construct()
    {
        $this->uuid = uniqid();
        $this->partials = new ArrayCollection();
    }


}