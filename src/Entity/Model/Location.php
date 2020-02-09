<?php
namespace App\Entity\Model;

/**
 * Implement this to add a Latitude,Longitude Columns
 */
interface Location
{
    /**
     * @return string
     */
    public function getLatitude();

    /**
     * @param string $l
     */
    public function setLatitude(string $l);

    /**
     * @return string
     */
    public function getLongitude();

    /**
     * @param string $l
     */
    public function setLongitude(string $l);
}