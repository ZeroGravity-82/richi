<?php


namespace App\Service;

use App\Entity\Account;
use App\Entity\Operation;
use App\Repository\AccountRepository;
use App\Repository\OperationRepository;
use App\ValueObject\AccountBalance;
use App\ValueObject\FundBalance;
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

    /** @var OperationRepository */
    private $operationRepo;

    /** @var AccountRepository */
    private $accountRepo;

    /**
     * BalanceMonitor constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em            = $em;
        $this->operationRepo = $em->getRepository(Operation::class);
        $this->accountRepo   = $em->getRepository(Account::class);
    }

    /**
     * Returns array of account balances.
     *
     * @param UserInterface $user
     *
     * @return AccountBalance[]
     */
    public function getAccountBalances(UserInterface $user): array
    {
        $accountBalances = [];

        $accounts    = $this->accountRepo->findByUser($user);
        $inflowSums  = $this->operationRepo->getInflowSums($accounts);
        $outflowSums = $this->operationRepo->getOutflowSums($accounts);

        foreach ($accounts as $account) {
            // Consider initial balance
            $accountBalance = new AccountBalance($account, $account->getInitialBalance());

            // Consider inflows
            foreach ($inflowSums as $inflowSum) {
                if ($inflowSum->getAccount() !== $account) {
                    continue;
                }
                $inflowSum      = $inflowSum->getValue();
                $accountBalance = new AccountBalance($account, $accountBalance->getValue() + $inflowSum);
            }

            // Consider outflows
            foreach ($outflowSums as $outflowSum) {
                if ($outflowSum->getAccount() !== $account) {
                    continue;
                }
                $outflowSum     = $outflowSum->getValue();
                $accountBalance = new AccountBalance($account, $accountBalance->getValue() - $outflowSum);
            }

            $accountBalances[] = $accountBalance;
        }

        return $accountBalances;
    }

    /**
     * @param AccountBalance[] $accountBalances
     *
     * @return integer
     */
    public function calculateTotalBalance(array $accountBalances): int
    {
        $total = 0;

        foreach ($accountBalances as $accountBalance) {
            $total += $accountBalance->getValue();
        }

        return $total;
    }

    /**
     * Returns array of fund balances.
     *
     * @param UserInterface $user
     *
     * @return FundBalance[]
     */
    public function getFundBalances(UserInterface $user): array
    {
        $fundBalances = [];


        return $fundBalances;
    }
}
