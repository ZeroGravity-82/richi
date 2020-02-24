<?php

namespace App\Entity;

/**
 * Interface Identifiable
 * @package App\Entity
 */
interface Identifiable
{
    /**
     * Returns entity ID.
     *
     * @return integer|null
     */
    public function getId(): ?int;
}
