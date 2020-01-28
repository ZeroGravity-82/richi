<?php


namespace App\Service;

use App\Entity\Operation;
use App\Repository\OperationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class BalanceMonitor
 * @package App\Service
 */
class BalanceMonitor
{
    /** @var EntityManagerInterface */
    private $em;

    /**
     * BalanceMonitor constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param UserInterface $user
     *
     * @return integer
     */
    public function getCurrentStatus(UserInterface $user): int
    {
        /** @var OperationRepository $operationRepo */
        $operationRepo = $this->em->getRepository(Operation::class);
        $incomeSum     = $operationRepo->getIncomeSum($user);
        $expenseSum    = $operationRepo->getExpenseSum($user);

        return $incomeSum - $expenseSum;
    }
}
