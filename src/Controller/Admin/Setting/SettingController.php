<?php

namespace App\Controller\Admin\Setting;

use App\Entity\Setting;
use App\Entity\User;
use App\Form\Admin\SettingFormType;
use App\Repository\SettingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class SettingController extends AbstractController
{
    public function __construct(
        private readonly SettingRepository $settingRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/setting', name: 'app_admin_setting_index', methods: ['GET'])]
    public function index(): Response
    {
        $settings = $this->settingRepository->findAll();
        $setting = $settings[0];

        return $this->render('pages/admin/setting/index.html.twig', [
            'setting' => $setting,
        ]);
    }

    #[Route('/setting/{id<\d+>}/edit', name: 'app_admin_setting_edit', methods: ['GET', 'POST'])]
    public function edit(Setting $setting, Request $request): Response
    {
        $form = $this->createForm(SettingFormType::class, $setting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User */
            $admin = $this->getUser();

            $setting->setUser($admin);
            $setting->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($setting);
            $this->entityManager->flush();

            $this->addFlash('success', 'Les paramètres du site ont été modifiés');

            return $this->redirectToRoute('app_admin_setting_index');
        }

        return $this->render('pages/admin/setting/edit.html.twig', [
            'settingForm' => $form->createView(),
        ]);
    }
}
