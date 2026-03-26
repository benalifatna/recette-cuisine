<?php

namespace App\Controller\Visitor\Contact;

use App\Entity\Contact;
use App\Entity\User;
use App\Form\ContactFormType;
use App\Repository\SettingRepository;
use App\Service\SendEmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController
{
    public function __construct(
        private readonly SettingRepository $settingRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly SendEmailService $sendEmailService,
    ) {
    }

    #[Route('/contact', name: 'app_visitor_contact_index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $contact = new Contact();

        $form = $this->createForm(ContactFormType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User */
            $user = $this->getUser();

            if (null != $user) {
                $contact->setUser($user);
            }

            $contact->setCreatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($contact);
            $this->entityManager->flush();

            $this->sendEmailService->sendEmail([
                'sender_email' => 'sophie-lemoine@gmail.com',
                'sender_full_name' => 'Sophie Lemoine',
                'recipient_email' => 'sophie-lemoine@gmail.com',
                'subject' => 'Un nouveau message reçu sur votre blog',
                'html_template' => 'emails/contact_form_email.html.twig',
                'context' => [
                    'contact' => $contact,
                ],
            ]);

            $this->addFlash('success', 'Votre message a bien été envoyée. Je vous recontacterai dans les plus brefs délais.');

            return $this->redirectToRoute('app_visitor_contact_index');
        }

        $setting = $this->settingRepository->findAll()[0];

        return $this->render('pages/visitor/contact/index.html.twig', [
            'setting' => $setting,
            'contactForm' => $form->createView(),
        ]);
    }
}
