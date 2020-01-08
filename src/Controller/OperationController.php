<?php

namespace App\Controller;

use App\Entity\Operation;
use App\Entity\User;
use App\Form\OperationType;
use App\Repository\OperationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class OperationController
 * @package App\Controller
 *
 * @Route("/operation")
 */
class OperationController extends AbstractController
{
    /**
     * @Route("/", name="operation_index")
     *
     * @return Response
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var OperationRepository $operationRepo */
        $operationRepo = $this->getDoctrine()->getManager()->getRepository(Operation::class);
        $user          = $this->getUser();
        $operations    = $operationRepo->findByUser($user);

        return $this->render('operation/index.html.twig', [
            'operations' => $operations,
        ]);
    }

    /**
     * @Route("/new/income", name="operation_new_income", methods={"GET", "POST"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function newIncome(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $operation = new Operation();
        $form      = $this->createForm(OperationType::class, $operation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();
            /** @var Operation $operation */
            $operation = $form->getData();
            $operation->setUser($user);

            $em = $this->getDoctrine()->getManager();
            $em->persist($operation);
            $em->flush();

            return $this->redirectToRoute('operation_index');
        }

        return $this->render('operation/new_income.html.twig', [
            'operationForm' => $form->createView(),
        ]);

    }
}
