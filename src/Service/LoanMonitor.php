<?php

namespace App\Service;

use App\Entity\Operation;
use App\Entity\Person;
use App\Enum\OperationTypeEnum;
use App\Repository\OperationRepository;
use App\Repository\PersonRepository;
use App\ValueObject\PersonObligation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class LoanMonitor
 * @package App\Service
 */
class LoanMonitor
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var OperationRepository */
    private $operationRepo;

    /** @var PersonRepository */
    private $personRepo;

    /**
     * LoanMonitor constructor.
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
     *
     * @return PersonObligation[]
     */
    public function getLoanList(UserInterface $user): array
    {
        $loanList = [];

        $persons         = $this->personRepo->findByUser($user);
        $loans           = $this->operationRepo->getPersonObligations($persons, OperationTypeEnum::TYPE_LOAN);
        $debtCollections = $this->operationRepo->getPersonObligations($persons, OperationTypeEnum::TYPE_DEBT_COLLECTION);

        foreach ($persons as $person) {
            $personLoan = new PersonObligation($person, 0);

            // Consider loans
            foreach ($loans as $loan) {
                if ($loan->getPerson() !== $person) {
                    continue;
                }
                $personLoan = new PersonObligation($person, $personLoan->getValue() + $loan->getValue());
            }

            // Consider debt collections
            foreach ($debtCollections as $debtCollection) {
                if ($debtCollection->getPerson() !== $person) {
                    continue;
                }
                $personLoan =  new PersonObligation($person, $personLoan->getValue() - $debtCollection->getValue());
            }

            if ($personLoan->getValue()) {
                $loanList[] = $personLoan;
            }
        }

        return $loanList;
    }
}
