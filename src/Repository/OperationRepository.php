<?php

namespace App\Repository;

use App\Entity\Account;
use App\Entity\Fund;
use App\Entity\Operation;
use App\Enum\OperationTypeEnum;
use App\ValueObject\AccountCashFlowSum;
use App\ValueObject\FundCashFlowSum;
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
     * @param Account[] $accounts
     *
     * @return AccountCashFlowSum[]
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getAccountInflowSums(array $accounts): array
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
            $groupedInflows[] = new AccountCashFlowSum($account, $sum);
        }

        return $groupedInflows;
    }

    /**
     * Calculates the sum of all the outflows for the accounts provided.
     *
     * @param Account[] $accounts
     *
     * @return AccountCashFlowSum[]
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getAccountOutflowSums(array $accounts): array
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
            $groupedOutflows[] = new AccountCashFlowSum($account, $sum);
        }

        return $groupedOutflows;
    }

    /**
     * Calculates the sum of all the cash flows of the provided funds for the given operation type.
     *
     * @param Fund[]  $funds
     * @param integer $type
     *
     * @return FundCashFlowSum[]
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @see OperationTypeEnum
     */
    public function getFundCashFlowSums(array $funds, int $type): array
    {
        $groupedInflows = [];

        $connection = $this->getEntityManager()->getConnection();
        $sql = <<<   'SQL'
SELECT fund_id,
       SUM(amount) as sum
FROM operation
WHERE fund_id IN (?)
      AND type = (?)
GROUP BY fund_id
SQL;

        $fundIds = $this->getFundIds($funds);
        $stmt    = $connection->executeQuery($sql, [$fundIds, $type], [Connection::PARAM_INT_ARRAY]);
        foreach ($stmt->fetchAll() as $fundInflow) {
            $fundId           = $fundInflow['fund_id'];
            $sum              = $fundInflow['sum'];
            $fund             = $funds[$fundId];
            $groupedInflows[] = new FundCashFlowSum($fund, $sum);
        }

        return $groupedInflows;
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

    /**
     * Returns an ID array for provided funds.
     *
     * @param Fund[] $funds
     *
     * @return integer[]
     */
    private function getFundIds(array $funds): array
    {
        $fundIds = [];

        foreach ($funds as $fund) {
            $fundIds[] = $fund->getId();
        }

        return $fundIds;
    }
}
