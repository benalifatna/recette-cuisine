<?php

namespace App\Controller\Visitor\Blog;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Recipe;
use App\Entity\Tag;
use App\Entity\User;
use App\Form\CommentFormType;
use App\Repository\CategoryRepository;
use App\Repository\RecipeRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BlogController extends AbstractController
{
    public function __construct(
        private readonly RecipeRepository $recipeRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly TagRepository $tagRepository,
        private readonly PaginatorInterface $paginator,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/blog', name: 'app_visitor_blog_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $categories = $this->categoryRepository->findAll();
        $tags = $this->tagRepository->findAll();
        $query = $this->recipeRepository->findBy(['isPublished' => true], ['publishedAt' => 'DESC']);

        $recipes = $this->paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            10 /* limit per page */
        );

        return $this->render('pages/visitor/blog/index.html.twig', [
            'categories' => $categories,
            'tags' => $tags,
            'recipes' => $recipes,
        ]);
    }

    #[Route('/blog/recettes-filtre-par-categorie/{id<\d+>}/{slug}', name: 'app_visitor_blog_filter_by_category', methods: ['GET'])]
    public function filterRecipesByCategory(Category $category, Request $request): Response
    {
        $categories = $this->categoryRepository->findAll();
        $tags = $this->tagRepository->findAll();
        $query = $this->recipeRepository->findBy(['category' => $category, 'isPublished' => true], ['publishedAt' => 'DESC']);

        $recipes = $this->paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            10 /* limit per page */
        );

        return $this->render('pages/visitor/blog/index.html.twig', [
            'categories' => $categories,
            'tags' => $tags,
            'recipes' => $recipes,
        ]);
    }

    #[Route('/blog/recettes-filtre-par-tag/{id<\d+>}/{slug}', name: 'app_visitor_blog_filter_by_tag', methods: ['GET'])]
    public function filterRecipesByTag(Tag $tag, Request $request): Response
    {
        $categories = $this->categoryRepository->findAll();
        $tags = $this->tagRepository->findAll();
        $query = $this->recipeRepository->filterRecipesByTag($tag->getId());

        $recipes = $this->paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            10 /* limit per page */
        );

        return $this->render('pages/visitor/blog/index.html.twig', [
            'categories' => $categories,
            'tags' => $tags,
            'recipes' => $recipes,
        ]);
    }

    #[Route('/blog/recette/{id<\d+>}/{slug}', name: 'app_visitor_blog_recipe_show', methods: ['GET', 'POST'])]
    public function showRecipe(Recipe $recipe, Request $request): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //     if (!$this->isGranted('ROLE_USER')) {
            //         return $this->redirectToRoute('app_visitor_blog_recipe_show', [
            //             'id' => $recipe->getId(),
            //             'slug' => $recipe->getSlug(),
            //         ]);
            //     }

            /**
             * @var User
             */
            $user = $this->getUser();

            $comment->setRecipe($recipe);
            $comment->setUser($user);
            $comment->setIsActivated(true);
            $comment->setCreatedAt(new \DateTimeImmutable());
            $comment->setActivatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_visitor_blog_recipe_show', [
                'id' => $recipe->getId(),
                'slug' => $recipe->getSlug(),
            ]);
        }

        return $this->render('pages/visitor/blog/show.html.twig', [
            'recipe' => $recipe,
            'commentForm' => $form->createView(),
        ]);
    }
}
