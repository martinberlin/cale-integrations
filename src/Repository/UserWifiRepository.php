<?php
namespace App\Repository;

use App\Entity\UserWifi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserWifiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserWifi::class);
    }
    public function wifisForUser($user)
    {
        return $this->createQueryBuilder('w')
            ->where('w.user = :user')
            ->orderBy('w.sortPos', 'ASC')
            ->setParameter('user', $user)
            ;
    }
}
