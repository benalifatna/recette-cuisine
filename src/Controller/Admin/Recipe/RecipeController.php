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
        $recipes = $this->recipeRepository->findAll();

        return $this->render('pages/admin/recipe/index.html.twig', [
            'recipes' => $recipes,
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

    #[Route('/recipe/{id<\d+>}/edit', name: 'app_admin_recipe_edit', methods: ['GET', 'POST'])]
    public function edit(Recipe $recipe, Request $request): Response
    {
        $form = $this->createForm(RecipeFormType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var User
             */
            $admin = $this->getUser();

            $recipe->setUser($admin);
            $recipe->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($recipe);
            $this->entityManager->flush();

            $this->addFlash('success', 'La recette a été modifié avec succès.');

            return $this->redirectToRoute('app_admin_recipe_index');
        }

        return $this->render('pages/admin/recipe/edit.html.twig', [
            'recipeForm' => $form->createView(),
            'recipe' => $recipe,
        ]);
    }

    #[Route('/recipe/{id<\d+>}/delete', name: 'app_admin_recipe_delete', methods: ['POST'])]
    public function delete(Recipe $recipe, Request $request): Response
    {
        if ($this->isCsrfTokenValid("delete-recipe-{$recipe->getId()}", $request->request->get('csrf_token'))) {
            $this->entityManager->remove($recipe);
            $this->entityManager->flush();

            $this->addFlash('success', 'La recette a été supprimé');
        }

        return $this->redirectToRoute('app_admin_recipe_index');
    }

    #[Route('/recipe/{id<\d+>}/publish', name: 'app_admin_recipe_publish', methods: ['POST'])]
    public function publish(Recipe $recipe, Request $request): Response
    {
        if (!$this->isCsrfTokenValid("publish-recipe-{$recipe->getId()}", $request->request->get('csrf_token'))) {
            return $this->redirectToRoute('app_admin_recipe_index');
        }

        // Si la recette est non publié
        if (!$recipe->isPublished()) {
            // Publions-le
            $recipe->setIsPublished(true);

            // Mettons à jour sa date de publication
            $recipe->setPublishedAt(new \DateTimeImmutable());

            // Générons le message flash correspondant
            $this->addFlash('success', 'La recette a été publié.');
        } else {
            // Dans le cas contraire,

            // Retirons la recette de la liste des publications
            $recipe->setIsPublished(false);

            // Mettons à jour sa date de publication
            $recipe->setPublishedAt(null);

            // Générons le message flash correspondant
            $this->addFlash('success', 'La recette a été retiré de la liste des publications.');
        }

        // Demandons au manager des entités de sauvegarder les modifications apportées en base de données
        $this->entityManager->persist($recipe);
        $this->entityManager->flush();

        // Rediriger l'administrateur vers la route menant à la page de liste des recettes
        // Puis, arrêtons l'exécution du script.
        return $this->redirectToRoute('app_admin_recipe_index');
    }
}
