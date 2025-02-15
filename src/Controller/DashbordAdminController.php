<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashbordAdminController extends AbstractController
{
    #[Route('/dashbord/home', name: 'app_dashbord_home')]
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


}
