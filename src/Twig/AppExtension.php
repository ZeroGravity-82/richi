<?php

namespace App\Twig;

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
}
