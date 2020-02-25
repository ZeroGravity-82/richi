<?php

namespace App\Controller;

use App\Service\DebtMonitor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DebtAndLoanController
 * @package App\Controller
 *
 * @Route('/debt-and-loan')
 */
class DebtAndLoanController extends AbstractController
{
    /** @var DebtMonitor */
    private $debtMonitor;

    /**
     * DebtAndLoanController constructor.
     *
     * @param DebtMonitor $debtMonitor
     */
    public function __construct(DebtMonitor $debtMonitor)
    {
        $this->debtMonitor = $debtMonitor;
    }

    /**
     * @Route("/", name="dept_and_loan_index")
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user         = $this->getUser();
        $fundBalance  = $this->debtMonitor->calculateFundBalance($fundBalances);

        return $this->render('fund/index.html.twig', [
            'fundBalances' => $fundBalances,
            'fundBalance'  => $fundBalance,
        ]);
    }
}
