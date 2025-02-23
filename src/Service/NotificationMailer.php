<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\User;

class NotificationMailer
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Envoie un e-mail de notification à l'utilisateur donné.
     *
     * @param User $user L'utilisateur destinataire
     * @param string $messageContent Le contenu du message
     */
    public function sendNotificationEmail(User $user, string $messageContent): void
    {
        $recipientEmail = $user->getEmail();

        $email = (new Email())
            ->from('amroush123@gmail.com')  // Votre adresse Gmail
            ->to($recipientEmail)
            ->subject('Notification : Commentaire désactivé')
            ->text($messageContent);

        $this->mailer->send($email);
    }
}
