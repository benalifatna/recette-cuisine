<?php

namespace App\Controller\Admin\Recipe;

use App\Entity\Recipe;
use App\Entity\User;
use App\Form\Admin\RecipeFormType;
use App\Repository\CategoryRepository;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class RecipeController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly RecipeRepository $recipeRepository,
    ) {
    }

    #[Route('/recipe/list', name: 'app_admin_recipe_index', methods: ['GET'])]
    public function index(): Response
    {
        // $recipes = $this->recipeRepository->findAll();

        return $this->render('pages/admin/recipe/index.html.twig', [
            //  'recipes' => $recipes,
        ]);
    }

    #[Route('/recipe/create', name: 'app_admin_recipe_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        if (0 == $this->categoryRepository->count()) {
            $this->addFlash('warning', 'Vous devez créer au moins une catégorie afin de rédiger des recettes.');

            return $this->redirectToRoute('app_admin_category_index');
        }

        $recipe = new Recipe();

        $form = $this->createForm(RecipeFormType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                /**
                 * @var User
                 */

            $admin = $this->getUser();

            $recipe->setUser($admin);
            $recipe->setCreatedAt(new \DateTimeImmutable());
            $recipe->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($recipe);
            $this->entityManager->flush();

            $this->addFlash('success', 'La recette a été ajouté avec succès.');

            return $this->redirectToRoute('app_admin_recipe_index');
        }

        return $this->render('pages/admin/recipe/create.html.twig', [
            'recipeForm' => $form->createView(),
        ]);
    }

    #[Route('/recipe/{id<\d+>}/show', name: 'app_admin_recipe_show', methods: ['GET'])]
    public function show(Recipe $recipe): Response
    {
        return $this->render('pages/admin/recipe/show.html.twig', [
            'recipe' => $recipe,
        ]);
    }
}
