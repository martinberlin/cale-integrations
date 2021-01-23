<?php
namespace App\Repository;

use App\Entity\ShippingTracking;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ShippingTrackingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShippingTracking::class);
    }

    /**
     * Calculate total costs in a single query
     */
    public function totalCosts()
    {
        return $this->createQueryBuilder('c')
            ->select('SUM(c.costShip) as shipping, SUM(c.costHardware) as hardware, SUM(c.costManufacturing) as manufacturing')
            ->getQuery()
            ->getSingleResult(); // Only one row
    }

    /**
     * Get all non-archived shippings for a user
     * @return array
     */
    public function getForUser(User $user)
    {
        return $this->createQueryBuilder('s')
            ->where('s.archived=false')
            ->andWhere('s.user=:user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getArrayResult();
    }
}
