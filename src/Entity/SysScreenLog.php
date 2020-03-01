<?php
namespace App\Entity;

use App\Entity\Model\CreatedTimestamp;
use Doctrine\ORM\Mapping as ORM;

/**
 * Logs statistics for each screen
 * @ORM\Entity(repositoryClass="App\Repository\SysScreenLogRepository")
 * @ORM\Table(name="sys_screen_log")
 */
class SysScreenLog implements CreatedTimestamp
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(name="created", type="integer", nullable=false)
     * @var integer
     */
    private $created;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="sysScreenLogs")
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Screen", inversedBy="sysScreenLogs")
     * @ORM\JoinColumn(name="screen", referencedColumnName="uuid")
     */
    protected $screen;

    /**
     * Bytes transferred
     * @var integer
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $bytes;

    /**
     * millis spent creating the image
     * @var integer
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $millis;

    /**
     * @ORM\Column(type="string", length=24, nullable=true)
     */
    protected $internalIp;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $cached;


    public function __construct()
    {
        $this->created = time();
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
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $uid
     */
    public function setUser(User $u)
    {
        $this->user = $u;
    }

    /**
     * @return mixed
     */
    public function getScreen()
    {
        return $this->screen;
    }

    /**
     * @param mixed $screen
     */
    public function setScreen(Screen $screen)
    {
        $this->screen = $screen;
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
    public function setCreated(int $created)
    {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getBytes()
    {
        return $this->bytes;
    }

    /**
     * @param mixed $bytes
     */
    public function setBytes($bytes)
    {
        $this->bytes = $bytes;
    }

    /**
     * @return int
     */
    public function getMillis(): int
    {
        return $this->millis;
    }

    /**
     * @param int $millis
     */
    public function setMillis(int $millis)
    {
        $this->millis = $millis;
    }

    /**
     * @return boolean
     */
    public function isCached(): bool
    {
        return $this->cached;
    }

    /**
     * @param boolean $cached
     */
    public function setCached(bool $cached)
    {
        $this->cached = $cached;
    }

    /**
     * @return mixed
     */
    public function getInternalIp()
    {
        return $this->internalIp;
    }

    /**
     * @param mixed $internalIp
     */
    public function setInternalIp($internalIp)
    {
        $this->internalIp = $internalIp;
    }

}