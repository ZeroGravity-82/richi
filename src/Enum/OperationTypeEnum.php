<?php

namespace App\Enum;

/**
 * Class OperationTypeEnum
 * @package App\Enum
 */
final class OperationTypeEnum
{
    const TYPE_INCOME          = 1;
    const TYPE_EXPENSE         = 2;
    const TYPE_TRANSFER        = 3;
    const TYPE_DEBT            = 4;
    const TYPE_REPAYMENT       = 5;
    const TYPE_LOAN            = 6;
    const TYPE_DEBT_COLLECTION = 7;

    /** @var array User friendly named type */
    private static $typeNames = [
        self::TYPE_INCOME          => 'income',
        self::TYPE_EXPENSE         => 'expense',
        self::TYPE_TRANSFER        => 'transfer',
        self::TYPE_DEBT            => 'debt',
        self::TYPE_REPAYMENT       => 'repayment',
        self::TYPE_LOAN            => 'loan',
        self::TYPE_DEBT_COLLECTION => 'debt-collection',
    ];

    /**
     * Returns User friendly name for the operation type.
     *
     * @param integer $type
     *
     * @return string
     */
    public static function getTypeName(int $type): string
    {
        return self::$typeNames[$type];
    }

    /**
     * Returns an array of all the available operation types.
     *
     * @return array
     */
    public static function getAvailableTypes(): array
    {
        return array_keys(self::$typeNames);
    }

    /**
     * Returns operation type for the given operation name.
     *
     * @param string $name
     *
     * @return integer
     *
     * @throws \InvalidArgumentException When invalid operation name provided.
     */
    public static function getTypeByName(string $name): int
    {
        $type = array_search($name, self::$typeNames);
        if ($type === false) {
            throw new \InvalidArgumentException('Invalid operation name.');
        }

        return $type;
    }
}
