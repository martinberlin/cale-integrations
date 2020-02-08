<?php
namespace App\Entity\Model;

/**
 * Implement this to add a sort Column
 */
interface Sortable
{

    /**
     * Get sorting position
     *
     * @return int
     */
    public function getSortPos();

    /**
     * Set sorting position
     *
     * @param int $sort
     */
    public function setSortPos($sort);

}