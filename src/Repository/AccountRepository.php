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
            ->addOrderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->addIndexes($result);
    }

    /**
     * Returns an array of accounts with account ID as an index.
     *
     * @param Account[] $accounts
     *
     * @return Account[]
     */
    private function addIndexes(array $accounts): array
    {
        $indexedAccounts = [];

        foreach ($accounts as $account) {
            $indexedAccounts[$account->getId()] = $account;
        }

        return $indexedAccounts;
    }
}
