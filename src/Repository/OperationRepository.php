<?php

namespace App\Repository;

use App\Entity\Account;
use App\Entity\Fund;
use App\Entity\Identifiable;
use App\Entity\Operation;
use App\Entity\Person;
use App\Enum\OperationTypeEnum;
use App\ValueObject\AccountCash;
use App\ValueObject\FundCash;
use App\ValueObject\PersonObligation;
use DateTimeImmutable;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;
use PDO;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class OperationRepository
 * @package App\Repository
 */
class OperationRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Operation::class);
    }

    /**
     * Returns an operation list for the user.
     *
     * @param UserInterface $user
     * @param DateTimeImmutable $to
     * @param string|null $sortOrder
     *
     * @return Operation[]
     */
    public function findByUser(UserInterface $user, DateTimeImmutable $to, string $sortOrder = 'ASC'): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.user = :user')
            ->andWhere('o.date <= :to')
            ->setParameter('user', $user)
            ->setParameter('to', $to)
            ->orderBy('o.date', $sortOrder)
            ->addOrderBy('o.createdAt', $sortOrder)
            ->getQuery()
            ->getResult();
    }

    /**
     * Calculates the sum of all the inflows for the accounts provided.
     *
     * @param Account[] $accounts
     * @param DateTimeImmutable $to
     *
     * @return AccountCash[]
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getAccountInflowSums(array $accounts, DateTimeImmutable $to): array
    {
        $groupedInflows = [];

        $connection = $this->getEntityManager()->getConnection();
        $sql = <<< 'SQL'
SELECT target_id as account_id,
       SUM(amount) as sum
FROM operation
WHERE target_id IN (?) AND date <= ?
GROUP BY target_id
SQL;

        $accountIds = $this->getIds($accounts);
        $to = $this->stringifyDate($to);
        $stmt = $connection->executeQuery(
            $sql,
            [$accountIds, $to],
            [Connection::PARAM_INT_ARRAY, PDO::PARAM_STR]
        );
        foreach ($stmt->fetchAll() as $accountInflow) {
            $accountId        = $accountInflow['account_id'];
            $sum              = $accountInflow['sum'];
            $account          = $accounts[$accountId];
            $groupedInflows[] = new AccountCash($account, $sum);
        }

        return $groupedInflows;
    }

    /**
     * Calculates the sum of all the outflows for the accounts provided.
     *
     * @param Account[] $accounts
     * @param DateTimeImmutable $to
     *
     * @return AccountCash[]
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getAccountOutflowSums(array $accounts, DateTimeImmutable $to): array
    {
        $groupedOutflows = [];

        $connection = $this->getEntityManager()->getConnection();
        $sql = <<< 'SQL'
SELECT source_id as account_id,
       SUM(amount) as sum
FROM operation
WHERE source_id IN (?) AND date <= ?
GROUP BY source_id
SQL;

        $accountIds = $this->getIds($accounts);
        $to = $this->stringifyDate($to);
        $stmt = $connection->executeQuery(
            $sql,
            [$accountIds, $to],
            [Connection::PARAM_INT_ARRAY, PDO::PARAM_STR]
        );
        foreach ($stmt->fetchAll() as $accountOutflow) {
            $accountId         = $accountOutflow['account_id'];
            $sum               = $accountOutflow['sum'];
            $account           = $accounts[$accountId];
            $groupedOutflows[] = new AccountCash($account, $sum);
        }

        return $groupedOutflows;
    }

    /**
     * Calculates the sum of all the cash flows of the provided funds for the given operation type.
     *
     * @param Fund[]  $funds
     * @param integer $type
     * @param DateTimeImmutable $to
     *
     * @return FundCash[]
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @see OperationTypeEnum
     */
    public function getFundCashFlowSums(array $funds, int $type, DateTimeImmutable $to): array
    {
        $groupedInflows = [];

        $connection = $this->getEntityManager()->getConnection();
        $sql = <<< 'SQL'
SELECT fund_id,
       SUM(amount) as sum
FROM operation
WHERE fund_id IN (?) AND type = ? AND date <= ?
GROUP BY fund_id
SQL;

        $fundIds = $this->getIds($funds);
        $to = $this->stringifyDate($to);
        $stmt = $connection->executeQuery(
            $sql,
            [$fundIds, $type, $to],
            [Connection::PARAM_INT_ARRAY, PDO::PARAM_INT, PDO::PARAM_STR]
        );
        foreach ($stmt->fetchAll() as $fundCashFlow) {
            $fundId           = $fundCashFlow['fund_id'];
            $sum              = $fundCashFlow['sum'];
            $fund             = $funds[$fundId];
            $groupedInflows[] = new FundCash($fund, $sum);
        }

        return $groupedInflows;
    }

    /**
     * Return 30 days expenses for the user averaged over 3 months.
     *
     * @param UserInterface $user
     * @param DateTimeImmutable $to
     *
     * @return integer
     */
    public function getUserExpenseSumAveraged(UserInterface $user, DateTimeImmutable $to): int
    {
        $result = $this->createQueryBuilder('o')
            ->select('SUM(o.amount)')
            ->andWhere('o.user = :user')
            ->andWhere('o.type = :type')
            ->andWhere('o.fund IS NULL')
            ->andWhere('o.date > :from')
            ->andWhere('o.date <= :to')
            ->setParameter('user', $user)
            ->setParameter('type', OperationTypeEnum::TYPE_EXPENSE)
            ->setParameter('from', $to->modify('-90 days'))
            ->setParameter('to', $to)
            ->getQuery()
            ->getSingleScalarResult() / 3;

        return $result ?? 0;
    }

    /**
     * Return 30 days incomes for the user averaged over 3 months.
     *
     * @param UserInterface $user
     * @param DateTimeImmutable $to
     *
     * @return integer
     */
    public function getUserIncomeSum(UserInterface $user, DateTimeImmutable $to): int
    {
        $result = $this->createQueryBuilder('o')
            ->select('SUM(o.amount)')
            ->andWhere('o.user = :user')
            ->andWhere('o.type = :type')
            ->andWhere('o.fund IS NULL')
            ->andWhere('o.date > :from')
            ->andWhere('o.date <= :to')
            ->setParameter('user', $user)
            ->setParameter('type', OperationTypeEnum::TYPE_INCOME)
            ->setParameter('from', $to->modify('-90 days'))
            ->setParameter('to', $to)
            ->getQuery()
            ->getSingleScalarResult() / 3;

        return $result ?? 0;
    }

    /**
     * Calculates the sum of all the person obligations for the provided persons and the given operation type (debt or
     * loan).
     *
     * @param Person[] $persons
     * @param integer $type
     * @param DateTimeImmutable $to
     *
     * @return PersonObligation[]
     */
    public function getPersonObligations(array $persons, int $type, DateTimeImmutable $to): array
    {
        $groupedDebts = [];

        $connection = $this->getEntityManager()->getConnection();
        $sql = <<< 'SQL'
SELECT person_id,
       SUM(amount) as sum
FROM operation
WHERE person_id IN (?) AND type = ? AND date <= ?
GROUP BY person_id
SQL;

        $personIds = $this->getIds($persons);
        $to = $this->stringifyDate($to);
        $stmt      = $connection->executeQuery(
            $sql,
            [$personIds, $type, $to],
            [Connection::PARAM_INT_ARRAY, PDO::PARAM_INT, PDO::PARAM_STR]
        );
        foreach ($stmt->fetchAll() as $personObligation) {
            $personId       = $personObligation['person_id'];
            $sum            = $personObligation['sum'];
            $person         = $persons[$personId];
            $groupedDebts[] = new PersonObligation($person, $sum);
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

    /**
     * Returns formatted date string.
     *
     * @param DateTimeImmutable $date
     *
     * @return string
     */
    private function stringifyDate(DateTimeImmutable $date): string
    {
        return $date->format('Y-m-d');
    }
}
