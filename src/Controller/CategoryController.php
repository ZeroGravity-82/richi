<?php

namespace App\Controller;

use App\Entity\Category;
use App\Enum\OperationTypeEnum;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
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

        return $this->render('category/index.html.twig', [
            'categories' => $categories
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

        $category = new Category();
        $category->setOperationType($operationType);

        /** @var Form $form */
        $form = $this->createForm(CategoryType::class, $category, [
            'operation_type' => $operationType,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UserInterface $user */
            $user = $this->getUser();
            /** @var Category $category */
            $category = $form->getData();
            $category->setUser($user);

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
}
