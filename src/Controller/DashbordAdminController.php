<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\NotificationRepository;

final class DashbordAdminController extends AbstractController
{
    #[Route('/dashbord/home', name: 'admin_dashboard')]
    public function dashbord(): Response
    {
        return $this->render('templates_admin/dashboard/index.html.twig', [
            'controller_name' => 'DashbordAdminController',
        ]);
    }

    #[Route('/dashbord/requestDons', name: 'app_dashbord_requestDons')]
    public function index(): Response
    {
        return $this->render('templates_admin/ListDons_Request/List.html.twig', [
            'controller_name' => 'DashbordAdminController',
        ]);
    }

    // Exemple dans un DashboardController ou un MainController
#[Route('/home', name: 'dashboard')]
public function dashboard(NotificationRepository $notifRepo): Response
{
    $user = $this->getUser();
    // S’il n’y a pas d’utilisateur connecté, on renvoie un tableau vide
    $notifications = $user ? $notifRepo->findBy(['user' => $user], ['createdAt' => 'DESC']) : [];

    return $this->render('base.html.twig', [
        'notifications' => $notifications,
    ]);
}



}
