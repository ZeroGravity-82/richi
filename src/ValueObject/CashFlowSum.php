<?php

namespace App\ValueObject;

use App\Entity\Account;

/**
 * Class CashFlowSum
 * @package App\ValueObject
 */
class CashFlowSum
{
    /** @var Account */
    private $account;

    /** @var integer */
    private $value;

    /**
     * CashFlowSum constructor.
     *
     * @param Account $account
     * @param integer $value
     */
    public function __construct(Account $account, int $value)
    {
        $this->account = $account;
        $this->value   = $value;
    }

    /**
     * @return Account
     */
    public function getAccount(): Account
    {
        return $this->account;
    }

    /**
     * @return integer
     */
    public function getValue(): int
    {
        return $this->value;
    }
}
