<?php
namespace App\Repository;

use App\Entity\IntegrationApi;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;


class IntegrationApiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IntegrationApi::class);
    }

    public function QueryApisForUser(User $user) {
        return $this->createQueryBuilder('i')
            ->where('i.userApi IN (:userapis)')
            ->setParameter('userapis', $user->getUserApis());
    }
}
