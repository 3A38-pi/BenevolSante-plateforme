<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EvenementController extends AbstractController
{
    #[Route('/evenement', name: 'app_evenement')]
    public function index(): Response
    {
        return $this->render('evenement/index.html.twig', [
            'controller_name' => 'EvenementController',
        ]);
    }
    #[Route('/evenement/detailE', name: 'app_evenementdetailE')]
    public function index1(): Response
    {
        return $this->render('detailevenement/detailE.html.twig', [
            'controller_name' => 'EvenementController',
        ]);
    }
    #[Route('/evenement/form', name: 'app_evenementformE')]
    public function index2(): Response
    {
        return $this->render('formEvent/formE.html.twig', [
            'controller_name' => 'EvenementController',
        ]);
    }
}
