<?php

namespace App\Controller\Admin\Profile;

use App\Entity\User;
use App\Form\Admin\ProfileFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class ProfileController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        // private readonly UserPasswordHasherInterface $hasher,
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
}
