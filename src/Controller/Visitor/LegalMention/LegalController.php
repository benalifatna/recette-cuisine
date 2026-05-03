<?php

namespace App\Controller\Visitor\LegalMention;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LegalController extends AbstractController
{
    #[Route('/mention-legal', name: 'app_visitor_mention_legal')]
    public function index(): Response
    {
        return $this->render('pages/visitor/mention_legal/index.html.twig');
    }
}


