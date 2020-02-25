<?php

namespace App\Repository;

use App\Entity\Person;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class PersonRepository
 * @package App\Repository
 */
class PersonRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

    /**
     * Returns a person list for the user.
     *
     * @param UserInterface $user
     *
     * @return Person[]
     */
    public function findByUser(UserInterface $user): array
    {
        $result = $this->createQueryBuilder('p')
            ->andWhere('p.user = :user')
            ->setParameter('user', $user)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->addIndexes($result);
    }
}
