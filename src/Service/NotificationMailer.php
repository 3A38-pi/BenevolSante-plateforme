<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\User;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class NotificationMailer
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }
    public function sendNotificationEmail(User $user, string $messageContent): void
    {
        $recipientEmail = $user->getEmail();

        $email = (new Email())
            ->from('amroush123@gmail.com') 
            ->to($recipientEmail)
            ->subject('Notification : Commentaire désactivé')
            ->text(sprintf("Bonjour,\n\nVotre commentaire a été désactivé.\nContenu: %s\n\nCordialement,\nL’équipe.", $messageContent));

        $this->mailer->send($email);
    }




    public function sendSimpleEmail(string $from, string $to, string $subject, string $text): void
    {
        $email = (new Email())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->text($text);

        $this->mailer->send($email);
    }
}
