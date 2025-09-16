<?php
namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Issue|null find($id, $lockMode = null, $lockVersion = null)
 * @method Issue|null findOneBy(array $criteria, array $orderBy = null)
 * @method Issue[]    findAll()
 * @method Issue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param User $user
     * @return bool
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @brief HAS the user an active subscription (Has paid?)
     */
    public function hasSubscription(User $user) {
        $query = $this->createQueryBuilder('u')
            ->select('u.paidTill')
            ->where('u.id = :userid')
            ->setParameter('userid', $user->getId())
            ->getQuery();

        $paidTill = is_array($query->getSingleResult()) ? $query->getSingleResult()['paidTill'] : new \DateTime('yesterday');
        $dateNow = new \DateTime();
        return ($paidTill > $dateNow);
    }
}
