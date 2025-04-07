<?php
namespace App\Repository;

use App\Entity\UserApiAmpereSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

class UserApiAmpereSettingsRepository extends ServiceEntityRepository
{
    private $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, UserApiAmpereSettings::class);
        $this->entityManager = $entityManager;
    }

}
