<?php

namespace App\Controller;

use App\Entity\Dons;
use App\Form\AddDonsType;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DonsController extends AbstractController
{
    #[Route('/dons/form', name: 'dons_form')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer un utilisateur existant avec l'ID 2 (ou un autre ID existant)
        $user = $entityManager->getRepository(User::class)->find(2); 
    
        if (!$user) {
            throw $this->createNotFoundException("Aucun utilisateur trouvé avec l'ID 2.");
        }
    
        $don = new Dons();
        // Associer l'utilisateur trouvé à l'objet Dons
        $don->setDonneur($user); 
    
        $form = $this->createForm(AddDonsType::class, $don);
    
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Forcer la valeur de "valide" à false sans que l'utilisateur puisse le modifier
            $don->setValide(false); // Toujours false
    
            $entityManager->persist($don);
            $entityManager->flush();
    
            $this->addFlash('success', 'Don ajouté avec succès !');
            return $this->redirectToRoute('dons_list');
        }
    
        return $this->render('dons_form/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    
    

    #[Route('/dons/list', name: 'dons_list')]
    public function list(): Response
    {
        return $this->render('dons_list/ListDons.html.twig');
    }
}
