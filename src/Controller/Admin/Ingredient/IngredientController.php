<?php

namespace App\Controller\Admin\Ingredient;

use App\Entity\Ingredient;
use App\Form\Admin\IngredientFormType;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class IngredientController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly IngredientRepository $ingredientRepository,
    ) {
    }

    #[Route('/ingredient/list', name: 'app_admin_ingredient_index', methods: ['GET'])]
    public function index(): Response
    {
        $ingredients = $this->ingredientRepository->findAll();

        return $this->render('pages/admin/ingredient/index.html.twig', [
            'ingredients' => $ingredients,
        ]);
    }

    #[Route('/ingredient/create', name: 'app_admin_ingredient_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $ingredient = new Ingredient();

        $form = $this->createForm(IngredientFormType::class, $ingredient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ingredient->setCreatedAt(new \DateTimeImmutable());
            $ingredient->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($ingredient);
            $this->entityManager->flush();

            $this->addFlash('success', 'L\'ingredient a été ajouté à la liste');

            return $this->redirectToRoute('app_admin_ingredient_index');
        }

        return $this->render('pages/admin/ingredient/create.html.twig', [
            'ingredientForm' => $form->createView(),
        ]);
    }

    #[Route('/ingredient/{id<\d+>}/edit', name: 'app_admin_ingredient_edit', methods: ['GET', 'POST'])]
    public function edit(Ingredient $ingredient, Request $request): Response
    {
        // création du formulaire
        $form = $this->createForm(IngredientFormType::class, $ingredient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ingredient->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($ingredient);
            $this->entityManager->flush();

            $this->addFlash('success', 'L\'ingrédient a été modifié.');

            return $this->redirectToRoute('app_admin_ingredient_index');
        }

        return $this->render('pages/admin/ingredient/edit.html.twig', [
            'ingredient' => $ingredient,
            'ingredientForm' => $form->createView(),
        ]);
    }
}
