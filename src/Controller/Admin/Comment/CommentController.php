<?php

namespace App\Controller\Admin\Comment;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class CommentController extends AbstractController
{
    public function __construct(
        private readonly CommentRepository $commentRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/comment/list', name: 'app_admin_comment_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/admin/comment/index.html.twig', [
            'comments' => $this->commentRepository->findAll(),
        ]);
    }

    #[Route('/comment/{id<\d+>}/delete', name: 'app_admin_comment_delete', methods: ['POST'])]
    public function delete(Comment $comment, Request $request): Response
    {
        if ($this->isCsrfTokenValid("delete-comment-{$comment->getId()}", $request->request->get('csrf_token'))) {
            $this->entityManager->remove($comment);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le commentaire a été supprimé');
        }

        return $this->redirectToRoute('app_admin_comment_index');
    }

    #[Route('/commentaire/{id<\d+>}/activate', name: 'app_admin_comment_activate', methods: ['POST'])]
    public function activate(Comment $comment, Request $request): Response
    {
        if (!$this->isCsrfTokenValid("activate-comment-{$comment->getId()}", $request->request->get('csrf_token'))) {
            return $this->redirectToRoute('app_admin_comment_index');
        }

        // Si le commentaire est non publié
        if (!$comment->isActivated()) {
            // Publions-le
            $comment->setIsActivated(true);

            // Mettons à jour sa date de publication
            $comment->setActivatedAt(new \DateTimeImmutable());

            // Générons le message flash correspondant
            $this->addFlash('success', 'Le commentaire a été activé.');
        } else {
            // Dans le cas contraire,

            // Retirons l'article de la liste des publications
            $comment->setIsActivated(false);

            // Mettons à jour sa date de publication
            $comment->setActivatedAt(null);

            // Générons le message flash correspondant
            $this->addFlash('success', 'Le commentaire a été retiré désactivé.');
        }

        // Demandons au manager des entités de sauvegarder les modifications apportées en base de données
        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        // Rediriger l'administrateur vers la route menant à la page de listant les articles
        // Puis, arrêtons l'exécution du script.
        return $this->redirectToRoute('app_admin_comment_index');
    }
}
