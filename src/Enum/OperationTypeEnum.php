<?php

namespace App\Enum;

/**
 * Class OperationTypeEnum
 * @package App\Enum
 */
final class OperationTypeEnum
{
    const TYPE_INCOME   = 1;
    const TYPE_EXPENSE  = 2;
    const TYPE_TRANSFER = 3;

    /** @var array User friendly named type */
    private static $typeName = [
        self::TYPE_INCOME   => 'income',
        self::TYPE_EXPENSE  => 'expense',
        self::TYPE_TRANSFER => 'transfer',
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
        return self::$typeName[$type];
    }

    /**
     * Returns an array of all the available operation types.
     *
     * @return array
     */
    public static function getAvailableTypes(): array
    {
        return array_keys(self::$typeName);
    }
}
