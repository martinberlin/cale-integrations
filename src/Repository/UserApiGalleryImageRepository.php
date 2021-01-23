<?php
namespace App\Repository;

use App\Entity\IntegrationApi;
use App\Entity\User;
use App\Entity\UserApiGalleryImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
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

    public function getImageNext(User $user, IntegrationApi $api, bool $moveToNext)
    {
        $imageIndex = is_null($api->getGalleryIndex()) ? 1 : $api->getGalleryIndex();
        try {
            $images = $this->createQueryBuilder('d')
                ->where('d.user=:user')
                ->andWhere('d.intApi=:intapi')
                ->setParameter('user', $user)
                ->setParameter('intapi', $api)
                ->orderBy('d.position', 'ASC')
                ->getQuery()
                ->getResult();
            // Loop till find image and pick next:

            foreach ($images as $key => $i) {
                if ($imageIndex === $key) {
                    $key++;
                    if ($key<count($images)) {
                        $image = $images[$key];
                    } else {
                        $key = 0;
                        $image = $images[0];
                    }
                    break;
                }
            }
            // Move to next key:
            if ($moveToNext) {
                $api->setGalleryIndex($key);
                $this->entityManager->persist($api);
                $this->entityManager->flush();
            }

        } catch (NoResultException $e) {
            // If something failed just return 1st image
            $image = $this->createQueryBuilder('d')
                ->where('d.user=:user')
                ->andWhere('d.intApi=:intapi')
                ->andWhere('d.intApi=:intapi')
                ->andWhere('d.imageId=1')
                ->setParameter('user', $user)
                ->setParameter('intapi', $api)
                ->orderBy('d.position', 'ASC')
                ->getQuery()
                ->getSingleResult();
        }
        return $image;
    }

}
