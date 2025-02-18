<?php

namespace App\Controller;

use App\Entity\Dons;
use App\Form\AddDonsType;
use App\Entity\User;
use App\Entity\DemandeDons;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;


final class DonsController extends AbstractController
{
    #[Route('/dons/form', name: 'dons_form')]
    public function create(Request $request, #[Autowire('%image_dir%')] string $imageDir, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->find(2);

        if (!$user) {
            throw $this->createNotFoundException("Aucun utilisateur trouvé avec l'ID 2.");
        }

        $don = new Dons();
        $don->setDonneur($user);

        $form = $this->createForm(AddDonsType::class, $don);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $don->setValide(false); // Le don doit être validé avant d'être affiché

            // Gestion de l'upload de l'image
            if ($image = $form['image']->getData()) {
                $filename = uniqid() . '.' . $image->guessExtension();
                $image->move($imageDir, $filename);
                $don->setImageUrl($filename);
            }

            $entityManager->persist($don);
            $entityManager->flush();

            $this->addFlash('success', 'Nouveau don ajouté à vérifier !');
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
    public function acceptedDons(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $repository = $entityManager->getRepository(Dons::class);
        $criteria = ['valide' => true];

        // Récupérer les catégories distinctes
        $categories = $repository->createQueryBuilder('d')
            ->select('DISTINCT d.categorie')
            ->getQuery()
            ->getResult();

        $queryBuilder = $repository->createQueryBuilder('d')
            ->where('d.valide = :valide')
            ->setParameter('valide', true);

        // Filtrage par catégorie
        if ($request->query->get('categorie')) {
            $queryBuilder->andWhere('d.categorie = :categorie')
                ->setParameter('categorie', $request->query->get('categorie'));
        }

        // Recherche par titre ou description
        if ($request->query->get('search')) {
            $search = $request->query->get('search');
            $queryBuilder->andWhere('d.titre LIKE :search OR d.description LIKE :search')
                ->setParameter('search', "%$search%");
        }

        $dons = $queryBuilder->getQuery()->getResult();

        // Récupérer les demandes de l'utilisateur connecté (si connecté)
        $demandes = [];
        $user = $security->getUser();
        if ($user) {
            $demandesList = $entityManager->getRepository(DemandeDons::class)->findBy(['beneficiaire' => $user]);
            foreach ($demandesList as $demande) {
                $demandes[$demande->getDons()->getId()] = $demande;
            }
        }

        return $this->render('/templates_users/dons_list/ListDons.html.twig', [
            'dons' => $dons,
            'categories' => array_column($categories, 'categorie'),
            'demandes' => $demandes, // Passer la variable au template
        ]);
    }








    #[Route('/demande-don', name: 'demande_don', methods: ['POST'])]
    public function demanderDon(
        Request $request,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        // Security $security
    ): JsonResponse {
        $logger->info("Requête reçue pour la création d'une demande de don.");

        try {
            // Récupérer l'ID du don depuis la requête
            $donId = $request->request->get('donId');
            if (!$donId) {
                $logger->error("Aucun ID de don fourni dans la requête.");
                return new JsonResponse(['message' => 'Aucun ID de don fourni'], Response::HTTP_BAD_REQUEST);
            }

            // Log pour voir si le donId est bien reçu
            $logger->info("Don ID reçu : $donId");

            // Récupérer le don et le bénéficiaire
            $don = $entityManager->getRepository(Dons::class)->find($donId);
            // Remplace 5 par l'ID d'un vrai utilisateur connecté
            $beneficiaire = $entityManager->getRepository(User::class)->find(5);
            // $beneficiaire = $security->getUser(); // Utiliser l'utilisateur connecté si la sécurité est bien configurée

            // Vérifier si le don et le bénéficiaire existent
            if (!$don || !$beneficiaire) {
                $logger->error("Don ou utilisateur introuvable.");
                return new JsonResponse(['message' => 'Erreur, don ou utilisateur introuvable'], Response::HTTP_BAD_REQUEST);
            }

            // Vérifier si une demande "En attente" ou "Acceptée" existe déjà pour ce don et ce bénéficiaire
            $existingDemande = $entityManager->getRepository(DemandeDons::class)
                ->createQueryBuilder('d')
                ->where('d.dons = :don')
                ->andWhere('d.beneficiaire = :beneficiaire')
                ->andWhere('d.statut IN (:statuts)')
                ->setParameter('don', $don)
                ->setParameter('beneficiaire', $beneficiaire)
                ->setParameter('statuts', ['En attente', 'Acceptée'])
                ->getQuery()
                ->getOneOrNullResult();

            if ($existingDemande) {
                $logger->info("Une demande en attente ou acceptée existe déjà.", [
                    'demande_id' => $existingDemande->getId(),
                    'statut' => $existingDemande->getStatut(),
                ]);
                return new JsonResponse(['message' => 'Vous avez déjà une demande en attente ou acceptée pour ce don.'], Response::HTTP_CONFLICT);
            }

            // Création de la nouvelle demande
            $demande = new DemandeDons();
            $demande->setDons($don);
            $demande->setBeneficiaire($beneficiaire);
            $demande->setDateDemande(new \DateTime());
            $demande->setStatut('En attente');

            // Enregistrer la demande en base de données
            $entityManager->persist($demande);
            $entityManager->flush();

            // Log de succès
            $logger->info("Demande de don créée avec succès pour le don ID : $donId");

            // Retourner une réponse JSON de succès
            return new JsonResponse(['message' => 'Demande envoyée avec succès'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Log de l'erreur
            $logger->error("Erreur lors de la création de la demande de don : " . $e->getMessage());

            // Retourner une réponse JSON d'erreur
            return new JsonResponse(['message' => 'Erreur interne : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    /////// recuperer liste des demandes pour les Bénéficiaires : tjiiiblou list mtee3 7ajet elli tlabhom bch ychouf etat mtee3hom
    #[Route('/beneficiaire/mes-demandes', name: 'mes_demandes')]
    public function mesDemandes(Security $security, EntityManagerInterface $entityManager): Response
    {
        $beneficiaire = $entityManager->getRepository(User::class)->find(5);
        /*

    $beneficiaire = $security->getUser();

    if (!$beneficiaire) {
        throw $this->createAccessDeniedException();
    }*/

        $demandes = $entityManager->getRepository(DemandeDons::class)->findBy([
            'beneficiaire' => $beneficiaire
        ]);

        return $this->render('templates_users/listeDemande_Beneficiaire/listeDemande_beneficiaire.html.twig', [
            'demandes' => $demandes,
        ]);
    }



    //recuperer liste des demande pour le donneur ta3tiiih bch y accepti demande walla yrefusiiii
    #[Route('/donneur/gerer-demandes', name: 'gerer_demandes')]
    public function gererDemandes(EntityManagerInterface $entityManager, Security $security): Response
    {

        $donneur = $entityManager->getRepository(User::class)->find(2);
        /*
     $donneur = $security->getUser();
     if (!$donneur) {
         throw $this->createAccessDeniedException("Accès refusé.");
     }
        */
        $demandes = $entityManager->getRepository(DemandeDons::class)->findByDonneur($donneur);

        return $this->render('templates_users/demandes_recues/demandes_recues.html.twig', [
            'demandes' => $demandes
        ]);
    }

    //lel beneficiaire bch y accepti wala yrefusiiiii demande 
    #[Route('/demande/{id}/{action}', name: 'modifier_demande', methods: ['POST'])]
    public function modifierDemande(DemandeDons $demande, string $action, EntityManagerInterface $entityManager, Security $security): JsonResponse
    {

        $donneur = $entityManager->getRepository(User::class)->find(2);
        /*
        $donneur = $security->getUser();

        if ($donneur !== $demande->getDons()->getDonneur()) {
            return new JsonResponse(['message' => 'Accès refusé.'], Response::HTTP_FORBIDDEN);
        }
        */
        if ($action === 'accepter') {
            $demande->setStatut('Acceptée');
        } elseif ($action === 'refuser') {
            $demande->setStatut('Refusée');
        } else {
            return new JsonResponse(['message' => 'Action non valide.'], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return new JsonResponse(['message' => "Demande $action avec succès."], Response::HTTP_OK);
    }
}
