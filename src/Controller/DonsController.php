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
            return $this->redirectToRoute('dons_accepted');
        }

        return $this->render('/templates_users/dons_form/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    #[Route('/admin/dons/request', name: 'app_dashbord_requestDons')]
    public function requestDons(EntityManagerInterface $entityManager): Response
    {
        // Récupérer les dons en attente
        $dons = $entityManager->getRepository(Dons::class)->findBy(['valide' => false]);

        return $this->render('templates_admin/ListDons_Request/List.html.twig', [
            'dons' => $dons,
        ]);
    }




    // Route pour accepter un don
    #[Route('/admin/dons/accept/{id}', name: 'admin_accept_don')]
    public function acceptDon(Dons $don, EntityManagerInterface $entityManager): Response
    {
        $don->setValide(true); // Accepter le don
        $entityManager->flush();

        $this->addFlash('success', 'Le don a été accepté.');
        return $this->redirectToRoute('app_dashbord_requestDons');
    }

    // Route pour refuser un don (le supprimer ou le désactiver)
    #[Route('/admin/dons/refuse/{id}', name: 'admin_refuse_don')]
    public function refuseDon(Dons $don, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($don);
        $entityManager->flush();

        $this->addFlash('error', 'Le don a été refusé et supprimé.');
        return $this->redirectToRoute('app_dashbord_requestDons');
    }



    //////////////////////////////////////////////////////////
    //hedhi partiee l dons mteee3 l user 
    ///////////////////////////////////////////////////////////




    #[Route('/dons/accepted', name: 'dons_accepted')]
    public function acceptedDons(EntityManagerInterface $entityManager): Response
    {
        // Récupérer tous les dons acceptés (où valide = true)
        $dons = $entityManager->getRepository(Dons::class)->findBy(['valide' => true]);

        return $this->render('/templates_users/dons_list/ListDons.html.twig', [
            'dons' => $dons, // On envoie les dons à la vue
        ]);
    }
}
