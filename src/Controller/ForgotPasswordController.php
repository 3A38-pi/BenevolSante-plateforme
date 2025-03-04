<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class ForgotPasswordController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route("/forgot-password", name: "app_forgot_password")]
    public function forgotPassword(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');

            // Check if the user exists
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user) {
                // Generate a reset token
                $resetToken = bin2hex(random_bytes(32));

                // Save the token to the user entity
                $user->setResetToken($resetToken);
                $this->entityManager->flush();

                // Create the email transport
                $transport = Transport::fromDsn('smtp://amroush123@gmail.com:npcfowmbtolgyqfe@smtp.gmail.com:587');
                $mailer = new Mailer($transport);

                // Create and send the email
                $email = (new Email())
                    ->from('amroush123@gmail.com')
                    ->to($user->getEmail())
                    ->subject('Réinitialisation de votre mot de passe')
                    ->html($this->renderView('emails/reset_password.html.twig', [
                        'resetToken' => $resetToken,
                    ]));

                try {
                    $mailer->send($email);
                    error_log('Email sent successfully to ' . $user->getEmail());
                } catch (\Exception $e) {
                    error_log('Email sending error: ' . $e->getMessage());
                    $this->addFlash('error', 'Erreur lors de l\'envoi de l\'email.');
                }
            } else {
                $this->addFlash('error', 'Aucun utilisateur trouvé avec cette adresse email.');
            }
        }

        return $this->render('authentification/forgot_password.html.twig');
    }
}