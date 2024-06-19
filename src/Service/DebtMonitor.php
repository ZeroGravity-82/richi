<?php

namespace App\Service;

use App\Entity\Operation;
use App\Entity\Person;
use App\Enum\OperationTypeEnum;
use App\Repository\OperationRepository;
use App\Repository\PersonRepository;
use App\ValueObject\PersonObligation;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class DebtMonitor
 * @package App\Service
 */
class DebtMonitor
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var OperationRepository */
    private $operationRepo;

    /** @var PersonRepository */
    private $personRepo;

    /**
     * DebtMonitor constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em            = $em;
        $this->operationRepo = $em->getRepository(Operation::class);
        $this->personRepo    = $em->getRepository(Person::class);
    }

    /**
     * @param UserInterface $user
     * @param DateTimeImmutable $to
     *
     * @return PersonObligation[]
     */
    public function getDebtList(UserInterface $user, DateTimeImmutable $to): array
    {
        $debtList = [];

        $persons    = $this->personRepo->findByUser($user);
        $debts      = $this->operationRepo->getPersonObligations($persons, OperationTypeEnum::TYPE_DEBT, $to);
        $repayments = $this->operationRepo->getPersonObligations($persons, OperationTypeEnum::TYPE_REPAYMENT, $to);

        foreach ($persons as $person) {
            $personDebt = new PersonObligation($person, 0);

            // Consider debts
            foreach ($debts as $debt) {
                if ($debt->getPerson() !== $person) {
                    continue;
                }
                $personDebt = new PersonObligation($person, $personDebt->getValue() + $debt->getValue());
            }

            // Consider repayments
            foreach ($repayments as $repayment) {
                if ($repayment->getPerson() !== $person) {
                    continue;
                }
                $personDebt = new PersonObligation($person, $personDebt->getValue() - $repayment->getValue());
            }

            if ($personDebt->getValue()) {
                $debtList[] = $personDebt;
            }
        }

        return $debtList;
    }
}
