<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

// Catogories are fixed for now
//* @ORM\GeneratedValue(strategy="AUTO")
/**
 * @ORM\Entity(repositoryClass="App\Repository\ApiCategoryRepository")
 * @ORM\Table(name="app_api_category")
 * @UniqueEntity("name")
 * @ORM\HasLifecycleCallbacks
 */
class ApiCategory
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
    * @ORM\Column(type="integer", nullable=true)
    */
    protected $parent_id;

    /**
     * @var string
     * @ORM\Column(type="string", length=30, unique=true)
     */
    protected $name;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $hidden;

    public function __construct()
    {
        $this->hidden = true;
    }

    /**
     * @return bool
     */
    public function isHidden():?bool
    {
        return $this->hidden;
    }

    /**
     * @param bool $hidden
     */
    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }


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
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getParentId()
    {
        return $this->parent_id;
    }

    /**
     * @param mixed $parent_id
     */
    public function setParentId($parent_id): void
    {
        $this->parent_id = $parent_id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    // Add any other setting that could be useful to aply to a certain group of APIs
}