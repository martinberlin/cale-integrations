<?php
namespace App\Repository;

use App\Entity\IntegrationApi;
use App\Entity\User;
use App\Entity\UserApiFinancialChart;
use App\Entity\UserApiGalleryImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;


class UserApiFinancialChartRepository extends ServiceEntityRepository
{
    private $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, UserApiFinancialChart::class);
        $this->entityManager = $entityManager;
    }

}
