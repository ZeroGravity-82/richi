<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ReportController
 * @package App\Controller
 *
 * @Route("/report")
 */
class ReportController extends AbstractController
{
    /**
     * @Route("/", name="report_index", methods="GET")
     *
     * @return Response
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');


        return $this->render('report/index.html.twig', [

        ]);
    }
}
