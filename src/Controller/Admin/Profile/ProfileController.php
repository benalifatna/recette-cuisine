<?php

namespace App\Controller\Admin\Profile;

use App\Entity\User;
use App\Form\EditPasswordProfileFormType;
use App\Form\ProfileFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class ProfileController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
    }

    #[Route('/profile/index', name: 'app_admin_profile_index', methods: ['GET'])]
    public function index(): Response
    {
        // $admin = $this->getUser();

        return $this->render('pages/admin/profile/index.html.twig', [
            // "admin" => $admin
        ]);
    }

    #[Route('/profile/edit', name: 'app_admin_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request): Response
    {
        /**
         * @var User
         */
        // Récupération des données de l'admin connecté
        $admin = $this->getUser();

        $form = $this->createForm(ProfileFormType::class, $admin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $admin->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($admin);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le profil a bien été modifié');

            return $this->redirectToRoute('app_admin_profile_index');
        }

        return $this->render('pages/admin/profile/edit.html.twig', [
            'profileForm' => $form->createView(),
        ]);
    }

    #[Route('/profile/password-edit', name: 'app_admin_profile_password_edit', methods: ['GET', 'POST'])]
    public function editPassword(Request $request): Response
    {
        $form = $this->createForm(EditPasswordProfileFormType::class, null);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var User
             */
            $admin = $this->getUser();

            $formData = $form->getData();
            // dd($formData);

            $passwordHashed = $this->hasher->hashPassword($admin, $formData['password']);

            $admin->setPassword($passwordHashed);
            $admin->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($admin);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le mot de passe a bien été modifié');

            return $this->redirectToRoute('app_admin_profile_index');
        }

        return $this->render('pages/admin/profile/edit_password.html.twig', [
            'passwordProfileForm' => $form->createView(),
        ]);
    }
}
