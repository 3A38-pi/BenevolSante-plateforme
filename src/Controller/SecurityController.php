<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    #[Route('/logout', name: 'app_logout', methods: ['POST'])]
    public function logout(): void
    {
        // This code is never executed.
        // Symfony intercepts this route and handles the logout automatically.
        throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
