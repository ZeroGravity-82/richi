<?php

namespace App\Controller;

use App\Service\BalanceMonitor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class BalanceController
 * @package App\Controller
 *
 * @Route("/balance")
 */
class BalanceController extends AbstractController
{
    /** @var BalanceMonitor $balanceMonitor */
    private $balanceMonitor;

    /**
     * DefaultController constructor.
     *
     * @param BalanceMonitor $balanceMonitor
     */
    public function __construct(BalanceMonitor $balanceMonitor)
    {
        $this->balanceMonitor = $balanceMonitor;
    }

    /**
     * @Route("/", name="balance_index", methods={"GET"})
     *
     * @return Response
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user           = $this->getUser();
        $currentBalance = $this->balanceMonitor->getCurrentStatus($user);

        return $this->render('balance/index.html.twig', [
            'currentBalance' => $currentBalance,
        ]);
    }
}
