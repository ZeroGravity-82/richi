<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Form\TagType;
use App\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class TagController
 * @package App\Controller
 *
 * @Route("/tag")
 */
class TagController extends AbstractController
{
    /**
     * @Route("/", name="tag_index", methods="GET")
     *
     * @return Response
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var TagRepository $tagRepo */
        $tagRepo = $this->getDoctrine()->getRepository(Tag::class);
        $user    = $this->getUser();
        $tags    = $tagRepo->findByUser($user);

        return $this->render('tag/index.html.twig', [
            'tags' => $tags,
        ]);
    }

    /**
     * @Route("/new", name="tag_new", methods={"GET", "POST"})
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

        $tag = new Tag();
        $tag->setUser($user);

        $form    = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) { 
            /** @var Tag $tag */
            $tag = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($tag);
            $em->flush();

            return $this->redirectToRoute('tag_index');
        }

        return $this->render('tag/new.html.twig', [
            'tagForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/{id}", name="tag_edit", methods={"GET", "POST"})
     *
     * @param Tag     $tag
     * @param Request $request
     *
     * @return Response
     */
    public function edit(Tag $tag, Request $request): Response
    {
        $this->denyAccessUnlessGranted('TAG_EDIT', $tag);

        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $tag = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($tag);
            $em->flush();

            return $this->redirectToRoute('tag_index');
        }

        return $this->render('tag/edit.html.twig', [
            'tagForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="tag_delete", methods={"DELETE"})
     *
     * @param Tag $tag
     *
     * @return Response
     */
    public function delete(Tag $tag): Response
    {
        $this->denyAccessUnlessGranted('TAG_DELETE', $tag);

        $em = $this->getDoctrine()->getManager();
        $em->remove($tag);
        $em->flush();

        return new JsonResponse([       // TODO fix it
            'data' => 'Tag deleted successfully',
        ]);
    }
}
