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
            new TwigFilter('date_human', [$this, 'formatDateForHumans']),
        ];
    }

    /**
     * Formats value for a currency.
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
     * Returns for an operation of the given type.
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
     * Returns a human-readable representation of the date provided as a timestamp.
     *
     * @param integer $timestamp
     *
     * @return string
     */
    public function formatDateForHumans(int $timestamp): string
    {
        $userTimezone    = new \DateTimeZone('Asia/Novosibirsk');       // TODO refactor to get from user settings
        $dateFormatFull  = 'F d, Y';                                    // TODO refactor to get from user settings
        $dateFormatShort = 'F d';                                       // TODO refactor to get from user settings

        $date            = (\DateTime::createFromFormat('U', $timestamp));
        $today           = new \DateTime('now', $userTimezone);
        $yesterday       = new \DateTime('yesterday', $userTimezone);
        $dateYear        = $date->format('Y');
        $todayYear       = $today->format('Y');
        $dateFormat      = $dateYear === $todayYear ? $dateFormatShort : $dateFormatFull;
        $formattedDate   = date($dateFormat, $timestamp);

        $dateString      = $date->format($dateFormatFull);
        $todayString     = $today->format($dateFormatFull);
        $yesterdayString = $yesterday->format($dateFormatFull);
        switch ($dateString) {
            case $todayString:
                $formattedDate .= ' (today)';
                break;
            case $yesterdayString:
                $formattedDate .= ' (yesterday)';
                break;
        }

        return $formattedDate;
    }
}
