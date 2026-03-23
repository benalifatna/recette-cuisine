<?php

namespace App\Controller\Admin\User;

use App\Entity\User;
use App\Form\Admin\EditRolesUserFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class UserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/user/list', name: 'app_admin_user_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/admin/user/index.html.twig', [
            'users' => $this->userRepository->findAll(),
        ]);
    }

    #[Route('/user/{id<\d+>}/edit-roles', name: 'app_admin_user_edit', methods: ['GET', 'POST'])]
    public function editRoles(User $user, Request $request): Response
    {
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('app_admin_user_index');
        }

        $form = $this->createForm(EditRolesUserFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Les rôles de cet utilisateur ont été modifiés.');

            return $this->redirectToRoute('app_admin_user_index');
        }

        return $this->render('pages/admin/user/edit_roles.html.twig', [
            'userRolesForm' => $form->createView(),
        ]);
    }

    #[Route('/user/{id<\d+>}/delete', name: 'app_admin_user_delete', methods: ['POST'])]
    public function delete(User $user, Request $request): Response
    {
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('app_admin_user_index');
        }

        if ($this->isCsrfTokenValid("delete-user-{$user->getId()}", $request->request->get('csrf_token'))) {
            foreach ($user->getRecipes() as $recipe) {
                $recipe->setUser(null);
            }

            $this->addFlash('success', "L'utilisateur {$user->getFirstName()} {$user->getLastName()} a été supprimé");

            if ($this->getUser() == $user) {
                $this->container->get('security.token_storage')->setToken(null);
            }

            $this->entityManager->remove($user);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_user_index');
    }
}
