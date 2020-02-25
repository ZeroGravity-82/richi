<?php

namespace App\ValueObject;

use App\Entity\Person;

/**
 * Class PersonObligation
 * @package App\ValueObject
 */
class PersonObligation
{
    /** @var Person */
    private $person;

    /** @var integer */
    private $value;

    /**
     * PersonObligation constructor.
     *
     * @param Person  $person
     * @param integer $value
     */
    public function __construct(Person $person, int $value)
    {
        $this->person = $person;
        $this->value  = $value;
    }

    /**
     * @return Person
     */
    public function getPerson(): Person
    {
        return $this->person;
    }

    /**
     * @return integer
     */
    public function getValue(): int
    {
        return $this->value;
    }
}
