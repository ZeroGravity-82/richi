<?php

namespace App\Controller;

use App\Entity\Person;
use App\Form\PersonType;
use App\Repository\PersonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class PersonController
 * @package App\Controller
 *
 * @Route("/person")
 */
class PersonController extends AbstractController
{
    /**
     * @Route("/", name="person_index", methods={"GET"})
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var PersonRepository $personRepo */
        $personRepo = $this->getDoctrine()->getRepository(Person::class);
        $user       = $this->getUser();
        $persons    = $personRepo->findByUser($user);

        return $this->render('person/index.html.twig', [
            'persons' => $persons,
        ]);
    }

    /**
     * @Route("/new", name="person_new", methods={"GET", "POST"})
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

        $person = new Person();
        $person->setUser($user);

        $form    = $this->createForm(PersonType::class, $person);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Person $person */
            $person = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($person);
            $em->flush();

            return $this->redirectToRoute('person_index');
        }

        return $this->render('person/new.html.twig', [
            'personForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/{id}", name="person_edit", methods={"GET", "POST"})
     *
     * @param Person  $person
     * @param Request $request
     *
     * @return Response
     */
    public function edit(Person $person, Request $request): Response
    {
        $this->denyAccessUnlessGranted('PERSON_EDIT', $person);

        $form = $this->createForm(PersonType::class, $person);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $person = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($person);
            $em->flush();

            return $this->redirectToRoute('person_index');
        }

        return $this->render('person/edit.html.twig', [
            'personForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="person_delete", methods={"DELETE"})
     *
     * @param Person $person
     *
     * @return Response
     */
    public function delete(Person $person): Response
    {
        $this->denyAccessUnlessGranted('PERSON_DELETE', $person);

        $em = $this->getDoctrine()->getManager();
        $em->remove($person);
        $em->flush();

        return new JsonResponse([       // TODO fix it
            'data' => 'Person deleted successfully',
        ]);
    }
}
