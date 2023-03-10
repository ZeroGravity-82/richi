<?php

namespace App\Controller;

use App\Entity\Account;
use App\Form\AccountType;
use App\Service\BalanceMonitor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class AccountController
 * @package App\Controller
 *
 * @Route("/account")
 */
class AccountController extends AbstractController
{
    /** @var BalanceMonitor */
    private $balanceMonitor;

    /**
     * AccountController constructor.
     *
     * @param BalanceMonitor $balanceMonitor
     */
    public function __construct(BalanceMonitor $balanceMonitor)
    {
        $this->balanceMonitor = $balanceMonitor;
    }

    /**
     * @Route("/", name="account_index", methods="GET")
     *
     * @return Response
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user            = $this->getUser();
        $accountBalances = $this->balanceMonitor->getAccountBalances($user);
        $total           = $this->balanceMonitor->calculateTotal($accountBalances);
        $fundBalances    = $this->balanceMonitor->getFundBalances($user);
        $fundBalance     = $this->balanceMonitor->calculateFundBalance($fundBalances);

        return $this->render('account/index.html.twig', [
            'accountBalances' => $accountBalances,
            'total'           => $total,
            'fundBalances'    => $fundBalances,
            'fundBalance'     => $fundBalance,
        ]);
    }

    /**
     * @Route("/new", name="account_new", methods={"GET", "POST"})
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

        $account = new Account();
        $account->setUser($user);

        $form    = $this->createForm(AccountType::class, $account);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) { 
            /** @var Account $account */
            $account = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($account);
            $em->flush();

            return $this->redirectToRoute('account_index');
        }

        return $this->render('account/new.html.twig', [
            'accountForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/{id}", name="account_edit", methods={"GET", "POST"})
     *
     * @param Account $account
     * @param Request $request
     *
     * @return Response
     */
    public function edit(Account $account, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ACCOUNT_EDIT', $account);

        $form = $this->createForm(AccountType::class, $account);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $account = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($account);
            $em->flush();

            return $this->redirectToRoute('account_index');
        }

        return $this->render('account/edit.html.twig', [
            'accountForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="account_delete", methods={"DELETE"})
     *
     * @param Account $account
     *
     * @return Response
     */
    public function delete(Account $account): Response
    {
        $this->denyAccessUnlessGranted('ACCOUNT_DELETE', $account);

        $em = $this->getDoctrine()->getManager();
        $em->remove($account);
        $em->flush();

        return new JsonResponse([       // TODO fix it
            'data' => 'Account deleted successfully',
        ]);
    }
}
