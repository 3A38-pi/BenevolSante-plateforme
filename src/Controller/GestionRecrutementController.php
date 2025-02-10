<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GestionRecrutementController extends AbstractController{
    #[Route('/gestion/recrutement/form', name: 'app_gestion_recrutement_form')]
    public function index(): Response
    {
        return $this->render('gestion_recrutement_form/index.html.twig', [
            'controller_name' => 'GestionRecrutementController',
        ]);
    }
    #[Route('/gestion/recrutement/list', name: 'app_gestion_recrutement_list')]
    public function index1(): Response
    {
        return $this->render('gestion_recrutement_list/gestion_recrutement_list.html.twig', [
            'controller_name' => 'GestionRecrutementController',
        ]);
    }
    #[Route('/gestion/recrutement/details', name: 'app_gestion_recrutement_details')]
    public function index2(): Response
    {
        return $this->render('gestion_recrutement_details/details_recrutement.html.twig', [
            'controller_name' => 'GestionRecrutementController',
        ]);
    }
}
