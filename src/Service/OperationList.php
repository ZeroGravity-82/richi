<?php


namespace App\Service;

use App\Entity\Operation;
use App\Repository\OperationRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class OperationList
 * @package App\Service
 */
class OperationList
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var OperationRepository */
    private $operationRepo;

    /**
     * OperationList constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em            = $em;
        $this->operationRepo = $em->getRepository(Operation::class);
    }

    /**
     * Return operation list for the user grouped by days.
     *
     * @param UserInterface $user
     * @param DateTimeImmutable $to
     *
     * @return Operation[]
     */
    public function getGroupedByDays(UserInterface $user, DateTimeImmutable $to): array
    {
        $groupedOperations = [];

        $allOperations = $this->operationRepo->findByUser($user, $to, 'DESC');
        foreach ($allOperations as $operation) {
            $operationDate                                = $operation->getDate();

            // TODO replace with pagination
            if ($operationDate < $to->modify('-3 months')) {
                break;
            }
            $operationDateTimestamp                       = $operationDate->getTimestamp();
            $groupedOperations[$operationDateTimestamp][] = $operation;
        }

        return $groupedOperations;
    }
}
