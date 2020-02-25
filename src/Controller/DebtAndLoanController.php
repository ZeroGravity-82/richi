<?php

namespace App\Controller;

use App\Service\DebtMonitor;
use App\Service\LoanMonitor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DebtAndLoanController
 * @package App\Controller
 *
 * @Route("/debt-and-loan")
 */
class DebtAndLoanController extends AbstractController
{
    /** @var DebtMonitor */
    private $debtMonitor;

    /** @var LoanMonitor */
    private $loanMonitor;

    /**
     * DebtAndLoanController constructor.
     *
     * @param DebtMonitor $debtMonitor
     * @param LoanMonitor $loanMonitor
     */
    public function __construct(DebtMonitor $debtMonitor, LoanMonitor $loanMonitor)
    {
        $this->debtMonitor = $debtMonitor;
        $this->loanMonitor = $loanMonitor;
    }

    /**
     * @Route("/", name="debt_and_loan_index", methods={"GET"})
     *
     * @return Response
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        return $this->render('debt_and_loan/index.html.twig', [
            'debtList' => $this->debtMonitor->getDebtList($user),
            'loanList' => $this->loanMonitor->getLoanList($user),
        ]);
    }
}
