<?php

namespace App\Twig;

use App\Enum\OperationTypeEnum;
use App\Form\DataTransformer\KopecksToRublesTransformer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class AppExtension
 * @package App\Twig
 */
class AppExtension extends AbstractExtension
{
    /** @var KopecksToRublesTransformer */
    private $transformer;

    /**
     * AppExtension constructor.
     *
     * @param KopecksToRublesTransformer $transformer
     */
    public function __construct(KopecksToRublesTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('currency', [$this, 'formatCurrency']),
            new TwigFilter('operation_name', [$this, 'getOperationName']),
            new TwigFilter('format_date', [$this, 'formatDate']),
        ];
    }

    /**
     * Format value for a currency.
     *
     * @param integer $value
     *
     * @return string
     */
    public function formatCurrency(int $value): string
    {
        return sprintf('%.2f', $this->transformer->transform($value));
    }

    /**
     * Return for an operation of the given type.
     *
     * @param integer $operationType
     *
     * @return string
     */
    public function getOperationName(int $operationType): string
    {
        return OperationTypeEnum::getTypeName($operationType);
    }

    /**
     * Returns a string representation of the date provided as timestamp.
     *
     * @param integer $timestamp
     *
     * @return string
     */
    public function formatDate(int $timestamp): string
    {
        return date('d.m.Y', $timestamp);
    }
}
