<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class SendEmailService
{
    public function __construct(
        private readonly MailerInterface $mailer,
    ) {
    }

    /**
     * Permet d'envoyer un email.
     *
     * @param array<string, mixed> $data
     */
    public function sendEmail(array $data = []): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($data['sender_email'], $data['sender_full_name']))
            ->to($data['recipient_email'])
            ->subject($data['subject'])
            ->htmlTemplate('emails/contact_form_email.html.twig')
            ->context($data['context'])
        ;

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            throw $e;
        }
    }
}
