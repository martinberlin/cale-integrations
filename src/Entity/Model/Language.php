<?php
namespace App\Entity\Model;

/**
 * Implement this to add a Language Column
 */
interface Language
{
    /**
     * @return string 2 chars language (en)
     */
    public function getLanguage();

    /**
     * @param string $l
     */
    public function setLanguage(string $l);

}