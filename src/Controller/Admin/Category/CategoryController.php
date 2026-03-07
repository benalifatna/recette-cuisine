<?php

namespace App\Controller\Admin\Category;

use App\Entity\Category;
use App\Form\Admin\CategoryFormType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class CategoryController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CategoryRepository $categoryRepository,
    ) {
    }

    #[Route('/category/list', name: 'app_admin_category_index', methods: ['GET'])]
    public function index(): Response
    {
        $categories = $this->categoryRepository->findAll();

        return $this->render('pages/admin/category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/category/create', name: 'app_admin_category_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $category = new Category();

        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category->setCreatedAt(new \DateTimeImmutable());
            $category->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($category);
            $this->entityManager->flush();

            $this->addFlash('success', 'La catégorie a été ajoutée à la liste.');

            return $this->redirectToRoute('app_admin_category_index');
        }

        return $this->render('pages/admin/category/create.html.twig', [
            'categoryForm' => $form->createView(),
        ]);
    }

    #[Route('/category/{id<\d+>}/edit', name: 'app_admin_category_edit', methods: ['GET', 'POST'])]
    public function edit(Category $category, Request $request): Response
    {
        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($category);
            $this->entityManager->flush();

            $this->addFlash('success', 'La catégorie a été modifiée');

            return $this->redirectToRoute('app_admin_category_index');
        }

        return $this->render('pages/admin/category/edit.html.twig', [
            'categoryForm' => $form->createView(),
        ]);
    }

    #[Route('/category/{id<\d+>}/delete', name: 'app_admin_category_delete', methods: ['POST'])]
    public function delete(Category $category, Request $request): Response
    {
        if ($this->isCsrfTokenValid("delete-category-{$category->getId()}", $request->request->get('csrf_token'))) {
            $this->entityManager->remove($category);
            $this->entityManager->flush();

            $this->addFlash('success', 'La catégorie a été supprimée');
        }

        return $this->redirectToRoute('app_admin_category_index');
    }
}
