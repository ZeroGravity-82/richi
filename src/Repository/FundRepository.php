<?php

namespace App\Repository;

use App\Entity\Fund;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class FundRepository
 * @package App\Repository
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
