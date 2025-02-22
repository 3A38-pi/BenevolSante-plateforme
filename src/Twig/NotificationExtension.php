<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Repository\NotificationRepository;
use Symfony\Component\Security\Core\Security;

class NotificationExtension extends AbstractExtension
{
    private Security $security;
    private NotificationRepository $notificationRepo;

    public function __construct(Security $security, NotificationRepository $notificationRepo)
    {
        $this->security = $security;
        $this->notificationRepo = $notificationRepo;
    }

    public function getFunctions(): array
    {
        return [
            // On déclare une fonction Twig 'user_notifications'
            new TwigFunction('user_notifications', [$this, 'getUserNotifications']),
        ];
    }

    public function getUserNotifications(): array
    {
        // Récupère l'utilisateur connecté
        $user = $this->security->getUser();
        if (!$user) {
            return [];
        }
        // Récupère ses notifications
        return $this->notificationRepo->findBy(['user' => $user], ['createdAt' => 'DESC']);
    }
}
