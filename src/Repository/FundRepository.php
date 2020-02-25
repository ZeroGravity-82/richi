<?php

namespace App\Repository;

use App\Entity\Fund;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Fund|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fund|null findOneBy(array $criteria, array $orderBy = null)
 * @method Fund[]    findAll()
 * @method Fund[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FundRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fund::class);
    }

    /**
     * Returns a fund list for the user.
     *
     * @param UserInterface $user
     *
     * @return Fund[]
     */
    public function findByUser(UserInterface $user): array
    {
        $result = $this->createQueryBuilder('f')
            ->andWhere('f.user = :user')
            ->setParameter('user', $user)
            ->addOrderBy('f.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->addIndexes($result);
    }
}
