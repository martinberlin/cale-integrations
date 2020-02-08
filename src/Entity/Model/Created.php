<?php
namespace App\Entity\Model;

/**
 * Implement this to add a created date Column
 */
interface Created
{

    public function getCreated();

    public function setCreated(\DateTime $dateTime = null);

}