<?php

namespace App\Controller;

use App\Entity\Fund;
use App\Form\FundType;
use App\Service\BalanceMonitor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class FundController
 * @package App\Controller
 *
 * @Route("/fund")
 */
class FundController extends AbstractController
{
    /** @var BalanceMonitor */
    private $balanceMonitor;

    /**
     * FundController constructor.
     *
     * @param BalanceMonitor $balanceMonitor
     */
    public function __construct(BalanceMonitor $balanceMonitor)
    {
        $this->balanceMonitor = $balanceMonitor;
    }

    /**
     * @Route("/", name="fund_index")
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user         = $this->getUser();
        $fundBalances = $this->balanceMonitor->getFundBalances($user);
        $fundBalance  = $this->balanceMonitor->calculateFundBalance($fundBalances);

        return $this->render('fund/index.html.twig', [
            'fundBalances' => $fundBalances,
            'fundBalance'  => $fundBalance,
        ]);
    }

    /**
     * @Route("/new", name="fund_new", methods={"GET", "POST"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var UserInterface $user */
        $user = $this->getUser();

        $fund = new Fund();
        $fund->setUser($user);

        $form    = $this->createForm(FundType::class, $fund);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Fund $fund */
            $fund = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($fund);
            $em->flush();

            return $this->redirectToRoute('fund_index');
        }

        return $this->render('fund/new.html.twig', [
            'fundForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/{id}", name="fund_edit", methods={"GET", "POST"})
     *
     * @param Fund    $fund
     * @param Request $request
     *
     * @return Response
     */
    public function edit(Fund $fund, Request $request): Response
    {
        $this->denyAccessUnlessGranted('FUND_EDIT', $fund);

        $form = $this->createForm(FundType::class, $fund);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $fund = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($fund);
            $em->flush();

            return $this->redirectToRoute('fund_index');
        }

        return $this->render('fund/edit.html.twig', [
            'fundForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="fund_delete", methods={"DELETE"})
     *
     * @param Fund $fund
     *
     * @return Response
     */
    public function delete(Fund $fund): Response
    {
        $this->denyAccessUnlessGranted('FUND_DELETE', $fund);

        $em = $this->getDoctrine()->getManager();
        $em->remove($fund);
        $em->flush();

        return new JsonResponse([       // TODO fix it
            'data' => 'Fund deleted successfully',
        ]);
    }
}
