<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Messagerie;
use App\Entity\Dons;
use App\Entity\DemandeDons;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Security;
use App\Entity\User;




final class MessagerieController extends AbstractController
{



    #[Route('/messages', name: 'app_messages')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser(); // Bénéficiaire connecté

        // Récupérer toutes les demandes de dons validées où l'utilisateur est le bénéficiaire
        $demandesDons = $entityManager->getRepository(DemandeDons::class)->findBy([
            'beneficiaire' => $user,
            'chatActif' => true
        ]);

        // Récupérer les donneurs et leurs messages associés
        $conversations = [];
        foreach ($demandesDons as $demande) {
            $donneur = $demande->getDons()->getDonneur();
            $messages = $entityManager->getRepository(Messagerie::class)->findBy([
                'demandeDon' => $demande
            ]);

            $conversations[] = [
                'donneur' => $donneur,
                'demande' => $demande,
                'messages' => $messages
            ];
        }

        return $this->render('templates_users/messagerie/index.html.twig', [
            'conversations' => $conversations,
        ]);
    }


    


    #[Route('/message/send', name: 'app_message_send', methods: ['POST'])]
    public function sendMessage(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $expediteur = $this->getUser();
        if ($expediteur instanceof User) {
            $nomExpediteur = $expediteur->getNom(); }

        // Récupérer la demande de don
        $demandeDon = $entityManager->getRepository(DemandeDons::class)->find($request->get('demande_id'));
    
        if (!$demandeDon || !$demandeDon->isChatActif()) {
            return new JsonResponse(['error' => 'Demande de don introuvable ou chat inactif.'], 400);
        }
    
        // Déterminer le destinataire (bénéficiaire ou donneur)
        if ($expediteur === $demandeDon->getBeneficiaire()) {
            $destinataire = $demandeDon->getDons()->getDonneur();
        } elseif ($expediteur === $demandeDon->getDons()->getDonneur()) {
            $destinataire = $demandeDon->getBeneficiaire();
        } else {
            return new JsonResponse(['error' => 'Utilisateur non autorisé à envoyer un message.'], 403);
        }
    
        // Vérification du contenu du message
        $contenu = trim($request->get('contenu'));
        if (!$contenu) {
            return new JsonResponse(['error' => 'Le message ne peut pas être vide.'], 400);
        }
    
        // Création du message
        $message = new Messagerie();
        $message->setExpediteur($expediteur)
            ->setDestinataire($destinataire)
            ->setContenu($contenu)
            ->setDemandeDon($demandeDon)
            ->setDateEnvoi(new \DateTime());
    
        $entityManager->persist($message);
        $entityManager->flush();
    
        // ✅ Retourner toutes les données nécessaires pour l'affichage immédiat
        return new JsonResponse([
            'success' => true,
            'expediteur' => $nomExpediteur,   // Récupérer le nom de l'expéditeur
            'contenu' => $contenu,  // Contenu du message
            'date' => $message->getDateEnvoi()->format('d/m/Y H:i')  // Date formatée
        ]);
    }
    




    #[Route('/messages/donneur', name: 'app_messages_donneur')]
public function indexDonneur(EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser(); // Donneur connecté

    // Récupérer toutes les demandes de dons où le donneur est impliqué et le chat est actif
    $demandesDons = $entityManager->getRepository(DemandeDons::class)->findByChatActifForDonneur($user);

    $conversations = [];
    foreach ($demandesDons as $demande) {
        $beneficiaire = $demande->getBeneficiaire();
        $messages = $entityManager->getRepository(Messagerie::class)->findBy([
            'demandeDon' => $demande
        ], ['dateEnvoi' => 'ASC']);

        $conversations[] = [
            'beneficiaire' => $beneficiaire,
            'demande' => $demande,
            'messages' => $messages
        ];
    }

    return $this->render('templates_users/messagerie/donneur.html.twig', [
        'conversations' => $conversations,
    ]);
}

#[Route('/discussion/donneur/{demandeId}', name: 'app_discussion_donneur')]
public function discussionDonneur(int $demandeId, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser(); // Donneur connecté

    // Récupérer la demande de don
    $demande = $entityManager->getRepository(DemandeDons::class)->find($demandeId);

    if (!$demande || $demande->getDons()->getDonneur() !== $user) {
        throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette discussion.');
    }

    // Récupérer les messages de cette demande
    $messages = $entityManager->getRepository(Messagerie::class)->findBy(
        ['demandeDon' => $demande],
        ['dateEnvoi' => 'ASC']
    );

    return $this->render('templates_users/messagerie/donneur.html.twig', [
        'demande' => $demande,
        'messages' => $messages,
    ]);
}







}
