<?php

namespace App\Service;

use App\Entity\Operation;
use App\Entity\Person;
use App\Enum\OperationTypeEnum;
use App\Repository\OperationRepository;
use App\Repository\PersonRepository;
use App\ValueObject\PersonObligationSum;
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
     *
     * @return PersonObligationSum[]
     */
    public function getDebtList(UserInterface $user): array
    {
        $debtList = [];

        $persons    = $this->personRepo->findByUser($user);
        $debts      = $this->operationRepo->getPersonObligationSums($persons, OperationTypeEnum::TYPE_DEBT);
        $repayments = $this->operationRepo->getPersonObligationSums($persons, OperationTypeEnum::TYPE_REPAYMENT);

        dump($debts);

        return $debtList;
    }
}
