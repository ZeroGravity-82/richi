<?php

namespace App\Repository;

use App\Entity\Account;
use App\Entity\Operation;
use App\ValueObject\CashFlowSum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Operation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Operation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Operation[]    findAll()
 * @method Operation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OperationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Operation::class);
    }

    /**
     * Returns an operation list for the user.
     *
     * @param UserInterface $user
     * @param string|null   $sortOrder
     *
     * @return Operation[]
     */
    public function findByUser(UserInterface $user, string $sortOrder = 'ASC'): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.user = :user')
            ->setParameter('user', $user)
            ->orderBy('o.date', $sortOrder)
            ->addOrderBy('o.createdAt', $sortOrder)
            ->getQuery()
            ->getResult();
    }

    /**
     * Calculates the sum of all the inflows for the accounts provided.
     *
     * @param Account[]
     *
     * @return CashFlowSum[]
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getInflowSums(array $accounts): array
    {
        $groupedInflows = [];

        $connection = $this->getEntityManager()->getConnection();
        $sql = <<< 'SQL'
SELECT target_id as account_id,
       SUM(amount) as sum
FROM operation
WHERE target_id IN (?)
GROUP BY target_id
SQL;

        $accountIds = $this->getAccountIds($accounts);
        $stmt       = $connection->executeQuery($sql, [$accountIds], [Connection::PARAM_INT_ARRAY]);
        foreach ($stmt->fetchAll() as $accountInflow) {
            $accountId        = $accountInflow['account_id'];
            $sum              = $accountInflow['sum'];
            $account          = $accounts[$accountId];
            $groupedInflows[] = new CashFlowSum($account, $sum);
        }

        return $groupedInflows;
    }

    /**
     * Calculates the sum of all the outflows for the accounts provided.
     *
     * @param Account[] $accounts
     *
     * @return CashFlowSum[]
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getOutflowSumsForUser(array $accounts): array
    {
        $groupedOutflows = [];

        $connection = $this->getEntityManager()->getConnection();
        $sql = <<< 'SQL'
SELECT source_id as account_id,
       SUM(amount) as sum
FROM operation
WHERE source_id IN (?)
GROUP BY source_id
SQL;

        $accountIds = $this->getAccountIds($accounts);
        $stmt       = $connection->executeQuery($sql, [$accountIds], [Connection::PARAM_INT_ARRAY]);
        foreach ($stmt->fetchAll() as $accountOutflow) {
            $accountId         = $accountOutflow['account_id'];
            $sum               = $accountOutflow['sum'];
            $account           = $accounts[$accountId];
            $groupedOutflows[] = new CashFlowSum($account, $sum);
        }

        return $groupedOutflows;
    }

    /**
     * Returns an ID array for provided accounts.
     *
     * @param Account[] $accounts
     *
     * @return integer[]
     */
    private function getAccountIds(array $accounts): array
    {
        $accountIds = [];

        foreach ($accounts as $account) {
            $accountIds[] = $account->getId();
        }

        return $accountIds;
    }
}
