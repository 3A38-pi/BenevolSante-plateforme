<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PrendreRDVController extends AbstractController
{
    #[Route('/prendre/r/d/v', name: 'app_prendre_r_d_v')]
    public function index(): Response
    {
        return $this->render('prendreRDVform/index.html.twig', [
            'controller_name' => 'PrendreRDVController',
        ]);
    }
}
