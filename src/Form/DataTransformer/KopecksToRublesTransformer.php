<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class KopecksToRublesTransformer
 * @package App\Form\DataTransformer
 */
class KopecksToRublesTransformer implements DataTransformerInterface
{
    /**
     * Transforms kopecks (int) to rubles (float).
     *
     * @param int|null $value
     *
     * @return float
     */
    public function transform($value): float
    {
        return (float) ($value / 100);
    }

    /**
     * Transforms rubles (float) to kopecks (int).
     *
     * @param float|null $value
     *
     * @return int
     */
    public function reverseTransform($value): int
    {
        return (int) ($value * 100);
    }
}
