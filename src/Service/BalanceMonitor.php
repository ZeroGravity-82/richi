<?php


namespace App\Service;

use App\Entity\Account;
use App\Entity\Fund;
use App\Entity\Operation;
use App\Enum\OperationTypeEnum;
use App\Repository\AccountRepository;
use App\Repository\FundRepository;
use App\Repository\OperationRepository;
use App\ValueObject\AccountCash;
use App\ValueObject\FundCash;
use DateTime;
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

    /** @var FundRepository */
    private $fundRepo;

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
        $this->fundRepo      = $em->getRepository(Fund::class);
    }

    /**
     * Returns array of account balances.
     *
     * @param UserInterface $user
     *
     * @return AccountCash[]
     */
    public function getAccountBalances(UserInterface $user): array
    {
        $accountBalances = [];

        $accounts    = $this->accountRepo->findByUser($user);
        $inflowSums  = $this->operationRepo->getAccountInflowSums($accounts, new DateTime());
        $outflowSums = $this->operationRepo->getAccountOutflowSums($accounts, new DateTime());

        foreach ($accounts as $account) {
            // Consider initial balance
            $accountBalance = new AccountCash($account, $account->getInitialBalance());

            // Consider inflows
            foreach ($inflowSums as $inflowSum) {
                if ($inflowSum->getAccount() !== $account) {
                    continue;
                }
                $inflowSumValue = $inflowSum->getValue();
                $accountBalance = new AccountCash($account, $accountBalance->getValue() + $inflowSumValue);
            }

            // Consider outflows
            foreach ($outflowSums as $outflowSum) {
                if ($outflowSum->getAccount() !== $account) {
                    continue;
                }
                $outflowSumValue = $outflowSum->getValue();
                $accountBalance  = new AccountCash($account, $accountBalance->getValue() - $outflowSumValue);
            }

            $accountBalances[] = $accountBalance;
        }

        return $accountBalances;
    }

    /**
     * @param AccountCash[] $accountBalances
     *
     * @return integer
     */
    public function calculateTotal(array $accountBalances): int
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
     * @return FundCash[]
     */
    public function getFundBalances(UserInterface $user): array
    {
        $fundBalances = [];

        $funds       = $this->fundRepo->findByUser($user);
        $incomeSums  = $this->operationRepo->getFundCashFlowSums($funds, OperationTypeEnum::TYPE_INCOME);
        $expenseSums = $this->operationRepo->getFundCashFlowSums($funds, OperationTypeEnum::TYPE_EXPENSE);

        foreach ($funds as $fund) {
            // Consider initial balance
            $fundBalance = new FundCash($fund, $fund->getInitialBalance());

            // Consider incomes
            foreach ($incomeSums as $incomeSum) {
                if ($incomeSum->getFund() !== $fund) {
                    continue;
                }
                $incomeSumValue = $incomeSum->getValue();
                $fundBalance    = new FundCash($fund, $fundBalance->getValue() + $incomeSumValue);
            }

            // Consider expenses
            foreach ($expenseSums as $expenseSum) {
                if ($expenseSum->getFund() !== $fund) {
                    continue;
                }
                $expenseSumValue = $expenseSum->getValue();
                $fundBalance     = new FundCash($fund, $fundBalance->getValue() - $expenseSumValue);
            }

            $fundBalances[] = $fundBalance;
        }

        return $fundBalances;
    }

    /**
     * @param FundCash[] $fundBalances
     *
     * @return integer
     */
    public function calculateFundBalance(array $fundBalances): int
    {
        $total = 0;

        foreach ($fundBalances as $fundBalance) {
            $total += $fundBalance->getValue();
        }

        return $total;
    }
}
