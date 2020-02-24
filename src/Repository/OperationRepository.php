<?php

namespace App\Repository;

use App\Entity\Account;
use App\Entity\Fund;
use App\Entity\Identifiable;
use App\Entity\Operation;
use App\Entity\Person;
use App\Enum\OperationTypeEnum;
use App\ValueObject\AccountCashFlowSum;
use App\ValueObject\FundCashFlowSum;
use App\ValueObject\PersonObligationSum;
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

        $accountIds = $this->getIds($accounts);
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

        $accountIds = $this->getIds($accounts);
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
        $sql = <<< 'SQL'
SELECT fund_id,
       SUM(amount) as sum
FROM operation
WHERE fund_id IN (?)
      AND type = (?)
GROUP BY fund_id
SQL;

        $fundIds = $this->getIds($funds);
        $stmt    = $connection->executeQuery($sql, [$fundIds, $type], [Connection::PARAM_INT_ARRAY]);
        foreach ($stmt->fetchAll() as $fundCashFlow) {
            $fundId           = $fundCashFlow['fund_id'];
            $sum              = $fundCashFlow['sum'];
            $fund             = $funds[$fundId];
            $groupedInflows[] = new FundCashFlowSum($fund, $sum);
        }

        return $groupedInflows;
    }

    /**
     * Return sum for all the expenses of the user.
     *
     * @param UserInterface $user
     *
     * @return integer
     */
    public function getUserExpenseSum(UserInterface $user): int
    {
        return $this->createQueryBuilder('o')
            ->select('SUM(o.amount)')
            ->andWhere('o.user = :user')
            ->andWhere('o.type = :type')
            ->andWhere('o.fund IS NULL')
            ->setParameter('user', $user)
            ->setParameter('type', OperationTypeEnum::TYPE_EXPENSE)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Return sum for all the incomes of the user.
     *
     * @param UserInterface $user
     *
     * @return integer
     */
    public function getUserIncomeSum(UserInterface $user): int
    {
        return $this->createQueryBuilder('o')
            ->select('SUM(o.amount)')
            ->andWhere('o.user = :user')
            ->andWhere('o.type = :type')
            ->andWhere('o.fund IS NULL')
            ->setParameter('user', $user)
            ->setParameter('type', OperationTypeEnum::TYPE_INCOME)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Calculates the sum of all the person obligations for the provided persons and the given operation type.
     *
     * @param Person[] $persons
     * @param integer  $type
     *
     * @return PersonObligationSum[]
     */
    public function getPersonObligationSums(array $persons, int $type): array
    {
        $groupedDebts = [];

        $connection = $this->getEntityManager()->getConnection();
        $sql = <<< 'SQL'
SELECT person_id,
       SUM(amount) as sum
FROM operation
WHERE person_id IN (?)
      AND type = (?)
GROUP BY person_id
SQL;

        $personIds = $this->getIds($persons);
        $stmt      = $connection->executeQuery($sql, [$personIds, $type], [Connection::PARAM_INT_ARRAY]);
        foreach ($stmt->fetchAll() as $personObligation) {
            $personId       = $personObligation['person_id'];
            $sum            = $personObligation['sum'];
            $person         = $persons[$personId];
            $groupedDebts[] = new PersonObligationSum($person, $sum);
        }

        return $groupedDebts;
    }

    /**
     * Returns an ID array for provided identifiable entities.
     *
     * @param Identifiable[] $entities
     *
     * @return integer[]
     */
    private function getIds(array $entities): array
    {
        $ids = [];

        foreach ($entities as $entity) {
            if (!$entity instanceof Identifiable) {
                continue;
            }
            $ids[] = $entity->getId();
        }

        return $ids;
    }
}
