<?php

namespace App\Controller\Admin\Home;

use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\ContactRepository;
use App\Repository\LikeRepository;
use App\Repository\RatingRepository;
use App\Repository\RecipeRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class HomeController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly RecipeRepository $recipeRepository,
        private readonly TagRepository $tagRepository,
        private readonly CommentRepository $commentRepository,
        private readonly UserRepository $userRepository,
        private readonly ContactRepository $contactRepository,
        private readonly LikeRepository $likeRepository,
        private readonly RatingRepository $ratingRepository,
    ) {
    }

    #[Route('/home', name: 'app_admin_home', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/admin/home/index.html.twig', [
            'categories_counted' => $this->categoryRepository->count(),
            'recipes_counted' => $this->recipeRepository->count(),
            'tags_counted' => $this->tagRepository->count(),
            'comments_counted' => $this->commentRepository->count(),
            'users_counted' => $this->userRepository->count(),
            'contacts_counted' => $this->contactRepository->count(),
            'likes_counted' => $this->likeRepository->count(),
            'ratings_counted' => $this->ratingRepository->count(),
        ]);
    }
}
