<?php
namespace App\Entity\Model;

/**
 * Implement this to add a created unix timestamp Column
 */
interface CreatedTimestamp
{

    public function getCreated();

    public function setCreated(int $timestamp);

}