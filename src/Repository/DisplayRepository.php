<?php
namespace App\Repository;

use App\Entity\Display;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class DisplayRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Display::class);
    }

    public function orderByTypeAndSize()
    {
        return $this->createQueryBuilder('d')
            ->orderBy('d.type', 'ASC')
            ->addOrderBy('d.width', 'DESC')
            ;
    }

}
