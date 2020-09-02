<?php
namespace App\Repository;

use App\Entity\IntegrationApi;
use App\Entity\User;
use App\Entity\UserApiGalleryImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;


class UserApiGalleryImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserApiGalleryImage::class);
    }

}
