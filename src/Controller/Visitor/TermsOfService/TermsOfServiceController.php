<?php

namespace App\Controller\Visitor\TermsOfService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TermsOfServiceController extends AbstractController
{
    #[Route('/les-conditions-generale-dutilisation', name: 'app_visitor_terms_of_service', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/visitor/terms_of_service/index.html.twig');
    }

}
