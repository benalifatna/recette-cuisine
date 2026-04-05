<?php

namespace App\Controller\Visitor\Welcome;

use App\Repository\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WelcomeController extends AbstractController
{
    public function __construct(
        private readonly RecipeRepository $recipeRepository,
    ) {
    }

    #[Route('/', name: 'app_visitor_welcome', methods: ['GET'])]
    public function index(): Response
    {
        $recipes = $this->recipeRepository->findBy(['isPublished' => true], ['publishedAt' => 'DESC'], 3);

        return $this->render('pages/visitor/welcome/index.html.twig', [
            'recipes' => $recipes,
        ]);
    }
}
