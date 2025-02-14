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

    #[Route('/prendre-rdv', name: 'app_prendre_rdv')]
public function prendreRendezVous(Request $request, EntityManagerInterface $em): Response
{
    $rdv = new RendezVous();
    $form = $this->createForm(RendezVousType::class, $rdv);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->persist($rdv);
        $em->flush();

        $this->addFlash('success', 'Rendez-vous pris avec succès !');
        return $this->redirectToRoute('app_prendre_rdv');
    }

    return $this->render('rendez_vous/prendre_rdv.html.twig', [
        'form' => $form->createView(),
    ]);
}
}
