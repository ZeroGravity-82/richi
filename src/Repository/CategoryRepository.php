<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * Returns a category list for the user.
     *
     * @param UserInterface $user
     *
     * @return Category[]
     */
    public function findByUser(UserInterface $user): array
    {
        $result = $this->createQueryBuilder('c')
            ->andWhere('c.user = :user')
            ->setParameter('user', $user)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();

        $this->sortAsString($result);

        return $result;
    }

    /**
     * Returns a category list of the given operation type that are able to be a parent category for other categories.
     * Categories are related to the specified user.
     *
     * @param UserInterface $user
     * @param integer       $operationType
     *
     * @return Category[]
     * 
     * @see OperationTypeEnum
     */
    public function findAbleToBeParent(UserInterface $user, int $operationType): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.user = :user')
            ->andWhere('c.parent IS NULL')
            ->andWhere('c.operationType = :operationType')
            ->setParameter('user', $user)
            ->setParameter('operationType', $operationType)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns a category list of the given operation type for specified user.
     *
     * @param UserInterface $user
     * @param integer       $operationType
     *
     * @return Category[]
     *
     * @see OperationTypeEnum
     */
    public function findByOperationType(UserInterface $user, int $operationType): array
    {
        $result = $this->createQueryBuilder('c')
            ->andWhere('c.user = :user')
            ->andWhere('c.operationType = :operationType')
            ->setParameter('user', $user)
            ->setParameter('operationType', $operationType)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();

        $this->sortAsString($result);

        return $result;
    }

    /**
     * Sorts given array of categories as a strings. That is necessary as a category may have a parent category. To make
     * this approach working the Category class should implement __toString() method that considers a parent category
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
