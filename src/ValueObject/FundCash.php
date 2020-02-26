<?php

namespace App\ValueObject;

use App\Entity\Fund;

/**
 * Class FundCash
 * @package App\ValueObject
 */
class FundCash
{
    /** @var Fund */
    private $fund;

    /** @var integer */
    private $value;

    /**
     * FundCash constructor.
     *
     * @param Fund    $fund
     * @param integer $value
     */
    public function __construct(Fund $fund, int $value)
    {
        $this->fund  = $fund;
        $this->value = $value;
    }

    /**
     * @return Fund
     */
    public function getFund(): Fund
    {
        return $this->fund;
    }

    /**
     * @return integer
     */
    public function getValue(): int
    {
        return $this->value;
    }
}
