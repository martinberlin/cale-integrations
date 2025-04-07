<?php
namespace App\Repository;

use App\Entity\ApiLogAmpereDaily;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class ApiLogAmpereDailyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiLogAmpereDaily::class);
    }

}
