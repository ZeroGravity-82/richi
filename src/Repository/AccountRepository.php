<?php

namespace App\Repository;

use App\Entity\Account;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Account|null find($id, $lockMode = null, $lockVersion = null)
 * @method Account|null findOneBy(array $criteria, array $orderBy = null)
 * @method Account[]    findAll()
 * @method Account[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Account::class);
    }

    /**
     * Returns an account list for the user.
     *
     * @param UserInterface $user
     *
     * @return Account[]
     */
    public function findByUser(UserInterface $user): array
    {
        $result = $this->createQueryBuilder('a')
            ->andWhere('a.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        $this->sortAsString($result);

        return $result;
    }

    /**
     * Returns a list of accounts that are able to be a parent account for other accounts. Accounts are related to
     * the specified user.
     *
     * @param UserInterface $user
     *
     * @return Account[]
     */
    public function findAbleToBeParent(UserInterface $user): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.user = :user')
            ->andWhere('a.parent IS NULL')
            ->setParameter('user', $user)
            ->orderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Sorts given array of accounts as a strings. That is necessary as an account may have a parent account. To make
     * this approach working the Account class should implement __toString() method that considers a parent account
     * as well.
     *
     * @param array $result
     *
     * @return boolean
     */
    private function sortAsString(array &$result): bool
    {
        return sort($result, SORT_STRING);
    }
}
