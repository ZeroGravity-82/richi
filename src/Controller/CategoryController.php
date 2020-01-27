<?php

namespace App\Controller;

use App\Entity\Category;
use App\Enum\OperationTypeEnum;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class CategoryController
 * @package App\Controller
 *
 * @Route("/category")
 */
class CategoryController extends AbstractController
{
    /**
     * @Route("/", name="category_index", methods={"GET"})
     *
     * @return Response
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var CategoryRepository $categoryRepo */
        $categoryRepo = $this->getDoctrine()->getRepository(Category::class);
        $user         = $this->getUser();
        $categories   = $categoryRepo->findByUser($user);

        $incomeCategories = array_filter($categories, function ($category) {
            return $category->getOperationType() === OperationTypeEnum::TYPE_INCOME;
        });
        $expenseCategories = array_filter($categories, function ($category) {
            return $category->getOperationType() === OperationTypeEnum::TYPE_EXPENSE;
        });

        return $this->render('category/index.html.twig', [
            'incomeCategories'  => $incomeCategories,
            'expenseCategories' => $expenseCategories,
        ]);
    }

    /**
     * @Route("/new/{operationName}", name="category_new", methods={"GET", "POST"})
     *
     * @param string  operationName
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

        /** @var UserInterface $user */
        $user = $this->getUser();

        $category = new Category();
        $category->setOperationType($operationType);
        $category->setUser($user);

        /** @var Form $form */
        $form = $this->createForm(CategoryType::class, $category, [
            'operation_type' => $operationType,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Category $category */
            $category = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();

            return $this->redirectToRoute('category_index');
        }

        return $this->render('category/new.html.twig', [
            'categoryForm'  => $form->createView(),
            'operationName' => $operationName,
        ]);
    }

    /**
     * @Route("/edit/{id}", name="category_edit", methods={"GET", "POST"})
     *
     * @param Category $category
     * @param Request  $request
     *
     * @return Response
     */
    public function edit(Category $category, Request $request): Response
    {
        $this->denyAccessUnlessGranted('CATEGORY_EDIT', $category);

        $form = $this->createForm(CategoryType::class, $category, [
            'operation_type' => $category->getOperationType(),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();

            return $this->redirectToRoute('category_index');
        }

        $operationName = OperationTypeEnum::getTypeName($category->getOperationType());

        return $this->render('category/edit.html.twig', [
            'categoryForm'  => $form->createView(),
            'operationName' => $operationName,
            'parentName'    => $category->getParent() ? $category->getParent()->getName() : null,
        ]);
    }

    /**
     * @Route("/{id}", name="category_delete", methods={"DELETE"})
     *
     * @param Category $category
     *
     * @return Response
     */
    public function delete(Category $category): Response
    {
        $this->denyAccessUnlessGranted('CATEGORY_DELETE', $category);

        $em = $this->getDoctrine()->getManager();
        $em->remove($category);
        $em->flush();

        return new JsonResponse([       // TODO fix it
            'data' => 'Category deleted successfully',
        ]);
    }
}
