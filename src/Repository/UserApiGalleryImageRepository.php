<?php
namespace App\Repository;

use App\Entity\IntegrationApi;
use App\Entity\User;
use App\Entity\UserApiGalleryImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;


class UserApiGalleryImageRepository extends ServiceEntityRepository
{
    private $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, UserApiGalleryImage::class);
        $this->entityManager = $entityManager;
    }

    public function getLastImageData(User $user, IntegrationApi $api)
    {
        try {
            $lastImage = $this->createQueryBuilder('d')
                ->where('d.user=:user')
                ->andWhere('d.intApi=:intapi')
                ->setParameter('user', $user)
                ->setParameter('intapi', $api)
                ->orderBy('d.imageId', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleResult();
            if ($lastImage instanceof UserApiGalleryImage) {
                $imgInfo = [
                    'imageId' =>$lastImage->getImageId(),
                    'position'=>$lastImage->getPosition(),
                ];
            }
        } catch (NoResultException $e) {
            $imgInfo = ['imageId'=>0, 'position'=>-1];
        }
        return $imgInfo;
    }

    public function getGalleryImages(User $user, IntegrationApi $api)
    {
        return $this->createQueryBuilder('d')
            ->where('d.user=:user')
            ->andWhere('d.intApi=:intapi')
            ->setParameter('user', $user)
            ->setParameter('intapi', $api)
            ->orderBy('d.position', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }

    public function saveImage(UserApiGalleryImage $image) {
        $this->entityManager->persist($image);
        $this->entityManager->flush();
    }

}
