<?php

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class TagRepository
 * @package App\Repository
 */
class TagRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    /**
     * Returns a tag list for the user.
     *
     * @param UserInterface $user
     *
     * @return Tag[]
     */
    public function findByUser(UserInterface $user): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.user = :user')
            ->setParameter('user', $user)
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
