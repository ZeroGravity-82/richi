<?php

namespace App\Controller;

use App\Entity\Operation;
use App\Enum\OperationTypeEnum;
use App\Form\OperationType;
use App\Service\OperationList;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class OperationController
 * @package App\Controller
 *
 * @Route("/operation")
 */
class OperationController extends AbstractController
{
    /** @var OperationList */
    private $operationList;

    /**
     * OperationController constructor.
     *
     * @param OperationList $operationList
     */
    public function __construct(OperationList $operationList)
    {
        $this->operationList = $operationList;
    }

    /**
     * @Route("/", name="operation_index", methods={"GET"})
     *
     * @return Response
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user              = $this->getUser();
        $groupedOperations = $this->operationList->getGroupedByDays($user);

        return $this->render('operation/index.html.twig', [
            'groupedOperations'  => $groupedOperations,
        ]);
    }

    /**
     * @Route("/new/{operationName}", name="operation_new", methods={"GET", "POST"})
     *
     * @param string  $operationName
     * @param Request $request
     *
     * @return Response
     */
    public function new(string $operationName, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        try {
            $operationType = OperationTypeEnum::getTypeByName($operationName);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        $operation = new Operation();
        $operation->setType($operationType);

        $form = $this->createForm(OperationType::class, $operation, [
            'operation_type' => $operationType,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UserInterface $user */
            $user = $this->getUser();
            /** @var Operation $operation */
            $operation = $form->getData();
            $operation->setUser($user);

            $em = $this->getDoctrine()->getManager();
            $em->persist($operation);
            $em->flush();

            return $this->redirectToRoute('operation_index');
        }

        return $this->render('operation/new_'.$operationName.'.html.twig', [
            'operationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/{id}", name="operation_edit", methods={"GET", "POST"})
     *
     * @param Operation $operation
     * @param Request   $request
     *
     * @return Response
     */
    public function edit(Operation $operation, Request $request): Response
    {
        $this->denyAccessUnlessGranted('OPERATION_EDIT', $operation);

        $form = $this->createForm(OperationType::class, $operation, [
            'operation_type' => $operation->getType(),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $operation = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($operation);
            $em->flush();

            return $this->redirectToRoute('operation_index');
        }

        $operationType = $operation->getType();

        return $this->render('operation/edit_'.OperationTypeEnum::getTypeName($operationType).'.html.twig', [
            'operationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="operation_delete", methods={"DELETE"})
     *
     * @param Operation $operation
     *
     * @return Response
     */
    public function delete(Operation $operation): Response
    {
        $this->denyAccessUnlessGranted('OPERATION_DELETE', $operation);

        $em = $this->getDoctrine()->getManager();
        $em->remove($operation);
        $em->flush();

        return new JsonResponse([       // TODO fix it
            'data' => 'Operation deleted successfully',
        ]);
    }
}
