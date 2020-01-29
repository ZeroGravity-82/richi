<?php

namespace App\Repository;

use App\Entity\Operation;
use App\Enum\OperationTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
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
     * Calculates the sum of all the income operations for the user.
     *
     * @param UserInterface $user
     * @return integer
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getIncomeSum(UserInterface $user): int
    {
        return $this->createQueryBuilder('o')
            ->select('SUM(o.amount)')
            ->andWhere('o.type = :type')
            ->andWhere('o.user = :user')
            ->setParameter('type', OperationTypeEnum::TYPE_INCOME)
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Calculates the sum of all the expense operations for the user.
     *
     * @param UserInterface $user
     * @return integer
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getExpenseSum(UserInterface $user): int
    {
        return $this->createQueryBuilder('o')
            ->select('SUM(o.amount)')
            ->andWhere('o.type = :type')
            ->andWhere('o.user = :user')
            ->setParameter('type', OperationTypeEnum::TYPE_EXPENSE)
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    // /**
    //  * @return Operation[] Returns an array of Operation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Operation
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
