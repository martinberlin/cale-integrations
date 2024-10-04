<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Table
 * @ORM\Entity
 */
class PaymentLog
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="agreement", type="array", nullable=true)
     */
    protected $arrayStorage;

    /**
     * @ORM\Column(name="notes", type="text", length=65535, nullable=true)
     */
    protected $notes;

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getArrayStorage()
    {
        return $this->arrayStorage;
    }

    /**
     * @param array
     */
    public function setArrayStorage(array $a)
    {
        $this->arrayStorage = $a;
    }

    public function getNotes()
    {
        return $this->notes;
    }

    public function setNotes(string $notes)
    {
        $this->notes = $notes;
    }
}