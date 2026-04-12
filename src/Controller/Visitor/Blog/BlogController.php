<?php

namespace App\Controller\Visitor\Blog;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Ingredient;
use App\Entity\Like;
use App\Entity\Rating;
use App\Entity\Recipe;
use App\Entity\Tag;
use App\Entity\User;
use App\Form\CommentFormType;
use App\Repository\CategoryRepository;
use App\Repository\IngredientRepository;
use App\Repository\LikeRepository;
use App\Repository\RatingRepository;
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
        private readonly IngredientRepository $ingredientRepository,
        private readonly PaginatorInterface $paginator,
        private readonly EntityManagerInterface $entityManager,
        private readonly LikeRepository $likeRepository,
        private readonly RatingRepository $ratingRepository,
    ) {
    }

    #[Route('/blog', name: 'app_visitor_blog_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $categories = $this->categoryRepository->findAll();
        $tags = $this->tagRepository->findAll();
        $ingredients = $this->ingredientRepository->findAll();
        $query = $this->recipeRepository->findBy(['isPublished' => true], ['publishedAt' => 'DESC']);

        $recipes = $this->paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            10 /* limit per page */
        );

        return $this->render('pages/visitor/blog/index.html.twig', [
            'categories' => $categories,
            'tags' => $tags,
            'ingredients' => $ingredients,
            'recipes' => $recipes,
        ]);
    }

    #[Route('/blog/recettes-filtre-par-categorie/{id<\d+>}/{slug}', name: 'app_visitor_blog_filter_by_category', methods: ['GET'])]
    public function filterRecipesByCategory(Category $category, Request $request): Response
    {
        $categories = $this->categoryRepository->findAll();
        $tags = $this->tagRepository->findAll();
        $ingredients = $this->ingredientRepository->findAll();
        $query = $this->recipeRepository->findBy(['category' => $category, 'isPublished' => true], ['publishedAt' => 'DESC']);

        $recipes = $this->paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            10 /* limit per page */
        );

        return $this->render('pages/visitor/blog/index.html.twig', [
            'categories' => $categories,
            'tags' => $tags,
            'ingredients' => $ingredients,
            'recipes' => $recipes,
        ]);
    }

    #[Route('/blog/recettes-filtre-par-tag/{id<\d+>}/{slug}', name: 'app_visitor_blog_filter_by_tag', methods: ['GET'])]
    public function filterRecipesByTag(Tag $tag, Request $request): Response
    {
        $categories = $this->categoryRepository->findAll();
        $tags = $this->tagRepository->findAll();
        $ingredients = $this->ingredientRepository->findAll();
        $query = $this->recipeRepository->filterRecipesByTag($tag->getId());

        $recipes = $this->paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            10 /* limit per page */
        );

        return $this->render('pages/visitor/blog/index.html.twig', [
            'categories' => $categories,
            'tags' => $tags,
            'ingredients' => $ingredients,
            'recipes' => $recipes,
        ]);
    }
    // $query = $this->recipeRepository->findBy(['ingredient' => $ingredient, 'isPublished' => true], ['publishedAt' => 'DESC']);

    #[Route('/blog/recettes-filtre-par-ingredient/{id<\d+>}/{slug}', name: 'app_visitor_blog_filter_by_ingredient', methods: ['GET'])]
    public function filterRecipesByIngredient(Ingredient $ingredient, Request $request): Response
    {
        $categories = $this->categoryRepository->findAll();
        $tags = $this->tagRepository->findAll();
        $ingredients = $this->ingredientRepository->findAll();
        $query = $this->recipeRepository->filterRecipesByIngredient($ingredient->getId());

        $recipes = $this->paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            10 /* limit per page */
        );

        return $this->render('pages/visitor/blog/index.html.twig', [
            'categories' => $categories,
            'tags' => $tags,
            'ingredients' => $ingredients,
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
            if (!$this->isGranted('ROLE_USER')) {
                return $this->redirectToRoute('app_visitor_blog_recipe_show', [
                    'id' => $recipe->getId(),
                    'slug' => $recipe->getSlug(),
                ]);
            }

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

    #[Route('/blog/recette/{id<\d+>}/{slug}/aimer', name: 'app_visitor_blog_recipe_like', methods: ['GET'])]
    public function likeRecipe(Recipe $recipe): Response
    {
        /** @var User */
        $user = $this->getUser();

        if (null == $user) {
            return $this->json([
                'message' => "Veuillez vous connecter afin d'aimer cette recette",
            ], Response::HTTP_FORBIDDEN);
        }

        // Si la recette est déjà aimé,
        if ($recipe->isAlreadyLikedBy($user)) {
            // Récupérer le like en question
            $like = $this->likeRepository->findOneBy(['recipe' => $recipe, 'user' => $user]);

            // La supprimer de la base de données
            $this->entityManager->remove($like);
            $this->entityManager->flush();

            // Retourner le message correspondant ainsi que le nombre de likes mis à jour
            return $this->json([
                'message' => "Vous avez retiré votre like de la recette {$recipe->getTitle()}",
                'totalLikesUpdated' => $this->likeRepository->count(['recipe' => $recipe]),
                'isLiked' => false,
            ]);
        }
        // Dans le cas contraire,
        // Créer le nouveau like
        $like = new Like();

        // Initialiser ses propriétés
        $like->setUser($user);
        $like->setRecipe($recipe);
        $like->setCreatedAt(new \DateTimeImmutable());

        // Le sauvegarder en base de données
        $this->entityManager->persist($like);
        $this->entityManager->flush();

        // Retourner le message correspondant ainsi que le nombre de likes mis à jour
        return $this->json([
            'message' => "Vous avez liké la recette {$recipe->getTitle()}",
            'totalLikesUpdated' => $this->likeRepository->count(['recipe' => $recipe]),
            'isLiked' => true,
        ]);
    }

    #[Route('/blog/recette/{id<\d+>}}/{slug}/noter/{value}', name: 'app_visitor_blog_recipe_rating', methods: ['GET'])]
    public function ratingRecipe(Recipe $recipe, int $value): Response
    {
        /** @var User */
        $user = $this->getUser();

        // Si le visiteur n'est pas connecté, on envoie un fichier JSON, et message 403 erreur "accès refusé"
        if (null == $user) {
            return $this->json([
                'message' => 'Veuillez vous connecter pour noter la recette',
            ], Response::HTTP_FORBIDDEN);
        }

        $existingRating = $this->ratingRepository->findOneBy([
            'recipe' => $recipe,
            'user' => $user,
        ]);
        //  Si une note existe pour la recette émis par l'utilisateur, on met à jour la nouvelle note et la date de modification
        if ($existingRating) {
            $existingRating->setValue($value);
            $existingRating->setUpdatedAt(new \DateTimeImmutable());
        } else {
            // Dans le cas contraire, Créer le nouveau like
            $rating = new Rating();
            // Initialiser ses propriétés
            $rating->setUser($user);
            $rating->setRecipe($recipe);
            $rating->setValue($value);
            $rating->setCreatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($rating);
        }
        // Le sauvegarder en base de données
        $this->entityManager->flush();

        // Retourner le message correspondant ainsi que le nombre de notes mis à jour moyenne
        return $this->json([
            'message' => 'Note enregistrée',
            'userRating' => $value,
            'average' => $this->ratingRepository->getAverageForRecipe($recipe),
        ]);

        //     return $this->redirectToRoute('app_visitor_blog_recipe_show', [
        //         'id' => $recipe->getId(),
        //         'slug' => $recipe->getSlug(),
        //     ]);
    }
}
