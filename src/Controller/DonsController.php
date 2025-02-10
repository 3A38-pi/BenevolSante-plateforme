<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DonsController extends AbstractController
{

    #[Route('/dons/form', name: 'dons_form')]
    public function index(): Response
    {
        return $this->render('dons_form/index.html.twig', [
            'controller_name' => 'DonsController',
        ]);
    }

    #[Route('/dons/list', name: 'dons_list')]
    public function index1(): Response
    {
        return $this->render('dons_list/ListDons.html.twig', [
            'controller_name' => 'DonsController',
        ]);
    }
}
