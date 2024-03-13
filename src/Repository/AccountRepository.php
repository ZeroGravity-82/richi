<?php

namespace App\Repository;

use App\Entity\Account;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class AccountRepository
 * @package App\Repository
 */
class AccountRepository extends BaseRepository
{
    const CREDIT_CARD_ACCOUNT_ID = 10;

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
            ->andWhere('a.id <> :creditCardId') // TODO костыль, чтобы не учитывать кредитку в общей сумме средств (Total)
            ->setParameter('user', $user)
            ->setParameter('creditCardId', self::CREDIT_CARD_ACCOUNT_ID)
            ->addOrderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->addIndexes($result);
    }

    /**
     * Returns a list of not archived accounts for the user.
     *
     * @param UserInterface $user
     *
     * @return Account[]
     */
    public function findNotArchived(UserInterface $user): array
    {
        $result = $this->createQueryBuilder('a')
            ->andWhere('a.user = :user')
            ->andWhere('a.archived = false')
            ->setParameter('user', $user)
            ->addOrderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->addIndexes($result);
    }
}
