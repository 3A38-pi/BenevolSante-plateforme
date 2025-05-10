<?php

namespace App\Controller;

use App\Entity\Dons;
use App\Form\AddDonsType;
use App\Entity\User;
use App\Entity\DemandeDons;
use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use Psr\Log\LoggerInterface;
use Knp\Component\Pager\PaginatorInterface;
use App\Service\PdfGeneratorService;
use App\Repository\UserRepository;
use App\Repository\DemandeDonsRepository;
use App\Service\NotificationMailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;


final class DonsController extends AbstractController
{

    #[Route('/dons/form', name: 'dons_form')]
    public function create(
        Request $request,
        #[Autowire('%image_dir%')] string $imageDir,
        EntityManagerInterface $entityManager,
        Security $security
    ): Response {
        $user = $security->getUser();

        if (!$user) {
            $this->addFlash('error', "Vous devez être connecté pour ajouter un don.");
            return $this->redirectToRoute('app_login');
        }

        $don = new Dons();
        $don->setDonneur($user);

        $form = $this->createForm(AddDonsType::class, $don);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $don->setValide(false);

            if ($image = $form->get('imageUrl')->getData()) {
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                $extension = $image->guessExtension();

                if (!in_array($extension, $allowedExtensions)) {
                    $this->addFlash('error', "Format d'image non valide. Seuls JPG, PNG et GIF sont autorisés.");
                    return $this->redirectToRoute('dons_form');
                }

                $filename = uniqid() . '.' . $image->guessExtension();
                try {
                    $image->move($imageDir, $filename);
                    $don->setImageUrl($filename); // Stocker seulement le nom du fichier
                } catch (\Exception $e) {
                    $this->addFlash('error', "Erreur lors de l'upload de l'image.");
                    return $this->redirectToRoute('dons_form');
                }
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
        $dons = $entityManager->getRepository(Dons::class)->findBy(['valide' => false]);

        return $this->render('templates_admin/ListDons_Request/List.html.twig', [
            'dons' => $dons,
        ]);
    }


    #[Route('/admin/dons/accept/{id}', name: 'admin_accept_don')]
    public function acceptDon(Dons $don, EntityManagerInterface $entityManager): Response
    {
        $don->setValide(true);
        $entityManager->flush();

        $this->addFlash('success', 'Le don a été accepté.');
        return $this->redirectToRoute('app_dashbord_requestDons');
    }

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



    //hedhi liste principale mteee3 les dons 

    #[Route('/dons/accepted', name: 'dons_accepted')]
    public function acceptedDons(
        Request $request,
        EntityManagerInterface $entityManager,
        Security $security,
        PaginatorInterface $paginator
    ): Response {
        $repository = $entityManager->getRepository(Dons::class);
        $criteria = ['valide' => true];

        // hedhi recuperiii categorie elli mawjoudin mel base
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

        // $dons = $queryBuilder->getQuery()->getResult();
        // Paginer les résultats
        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            5 // Nombre d'éléments par page
        );

        // Récupérer les demandes de l'utilisateur connecté 
        $demandes = [];
        $user = $security->getUser();
        if ($user) {
            $demandesList = $entityManager->getRepository(DemandeDons::class)->findBy(['beneficiaire' => $user]);
            foreach ($demandesList as $demande) {
                $demandes[$demande->getDons()->getId()] = $demande;
            }
        }

        return $this->render('/templates_users/dons_list/ListDons.html.twig', [
            // 'dons' => $dons,
            'pagination' => $pagination,
            'categories' => array_column($categories, 'categorie'),
            'demandes' => $demandes,
        ]);
    }









    // pour effectuer la demande d'un don
    #[Route('/demande-don', name: 'demande_don', methods: ['POST'])]
    public function demanderDon(
        Request $request,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        Security $security
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

            $beneficiaire = $security->getUser();

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



            // Créer une notification pour le donneur
            $notification = new Notification();
            $notification->setMessage("Nouvelle demande de don pour votre don : " . $don->getTitre());
            $notification->setUser($don->getDonneur()); // Le donneur est le propriétaire du don
            $notification->setDemandeDons($demande); // Lier la notification à la demande de don
            $notification->setType(Notification::TYPE_DEMANDE_DONS); // Définir le type de notification

            $entityManager->persist($notification);
            $entityManager->flush();

            $logger->info("Demande de don créée avec succès pour le don ID : $donId");

            return new JsonResponse(['message' => 'Demande envoyée avec succès'], Response::HTTP_OK);
        } catch (\Exception $e) {

            $logger->error("Erreur lors de la création de la demande de don : " . $e->getMessage());

            return new JsonResponse(['message' => 'Erreur interne : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    /////// recuperer liste des demandes pour les Bénéficiaires : tjiiiblou list mtee3 7ajet elli tlabhom bch ychouf etat mtee3hom
    #[Route('/beneficiaire/mes-demandes', name: 'mes_demandes')]
    public function mesDemandes(Security $security, EntityManagerInterface $entityManager): Response
    {


        $beneficiaire = $security->getUser();

        if (!$beneficiaire) {
            throw $this->createAccessDeniedException();
        }

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


        $donneur = $security->getUser();
        if (!$donneur) {
            throw $this->createAccessDeniedException("Accès refusé.");
        }

        $demandes = $entityManager->getRepository(DemandeDons::class)->findByDonneur($donneur);

        return $this->render('templates_users/demandes_recues/demandes_recues.html.twig', [
            'demandes' => $demandes
        ]);
    }

    #[Route('/demande/{id}/{action}', name: 'modifier_demande', methods: ['POST'])]
    public function modifierDemande(
        DemandeDons $demande,
        string $action,
        EntityManagerInterface $entityManager,
        Security $security,
    ): JsonResponse {
        try {

            $donneur = $security->getUser();


            if (!$donneur) {
                return new JsonResponse(['message' => 'Utilisateur introuvable.'], Response::HTTP_NOT_FOUND);
            }

            // Vérifier que l'utilisateur est le donneur du don
            if ($donneur !== $demande->getDons()->getDonneur()) {
                return new JsonResponse(['message' => 'Accès refusé.'], Response::HTTP_FORBIDDEN);
            }

            // Traiter l'action (accepter ou refuser)
            if ($action === 'accepter') {
                $demande->setStatut('Acceptée');
                $demande->activerChat();

                // Créer une notification pour le bénéficiaire
                $notificationBeneficiaire = new Notification();
                $notificationBeneficiaire->setMessage("Votre demande de don pour le don : " . $demande->getDons()->getTitre() . " a été acceptée.");
                $notificationBeneficiaire->setUser($demande->getBeneficiaire()); // Notification pour le bénéficiaire
                $notificationBeneficiaire->setDemandeDons($demande);
                $notificationBeneficiaire->setType(Notification::TYPE_DEMANDE_DONS);

                $entityManager->persist($notificationBeneficiaire);
                $entityManager->persist($demande);
                $entityManager->flush();
            } elseif ($action === 'refuser') {
                $demande->setStatut('Refusée');

                // Créer une notification pour le bénéficiaire
                $notificationBeneficiaire = new Notification();
                $notificationBeneficiaire->setMessage("Votre demande de don pour le don : " . $demande->getDons()->getTitre() . " a été refusée.");
                $notificationBeneficiaire->setUser($demande->getBeneficiaire()); // Notification pour le bénéficiaire
                $notificationBeneficiaire->setDemandeDons($demande);
                $notificationBeneficiaire->setType(Notification::TYPE_DEMANDE_DONS);

                $entityManager->persist($notificationBeneficiaire);
                $entityManager->persist($demande);
                $entityManager->flush();
            } else {
                return new JsonResponse(['message' => 'Action non valide.'], Response::HTTP_BAD_REQUEST);
            }

            return new JsonResponse(['message' => "Demande $action avec succès."], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Une erreur est survenue : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/demande/valider/{id}/{action}', name: 'valider_demande', methods: ['POST'])]
    public function validerDemande(
        DemandeDons $demande,
        string $action,
        EntityManagerInterface $entityManager,
        Security $security,
        NotificationMailer $notificationMailer
    ): JsonResponse {
        try {
            // Récupérer l'utilisateur connecté (le donneur)
            $donneur = $security->getUser();

            if (!$donneur) {
                return new JsonResponse(['message' => 'Vous devez être connecté pour effectuer cette action.'], Response::HTTP_UNAUTHORIZED);
            }

            // Vérifier que la demande de don est bien liée à un don
            if (!$demande->getDons()) {
                return new JsonResponse(['message' => 'Le don associé à cette demande est introuvable.'], Response::HTTP_NOT_FOUND);
            }

            // Vérifier que l'utilisateur est bien le donneur du don
            if ($donneur !== $demande->getDons()->getDonneur()) {
                return new JsonResponse(['message' => 'Accès refusé. Vous n\'êtes pas le propriétaire de ce don.'], Response::HTTP_FORBIDDEN);
            }

            // Vérifier si la demande est déjà validée
            if ($demande->getStatut() === 'Validée') {
                return new JsonResponse(['message' => 'Cette demande est déjà validée.'], Response::HTTP_BAD_REQUEST);
            }

            // Traiter l'action demandée
            if ($action === 'validee') {
                if ($demande->getStatut() !== 'Acceptée') {
                    return new JsonResponse(['message' => 'La demande doit être acceptée avant de pouvoir être validée.'], Response::HTTP_BAD_REQUEST);
                }

                // Valider la demande
                $demande->setStatut('Validée');

                // Créer une notification pour le bénéficiaire
                $notificationBeneficiaire = new Notification();
                $notificationBeneficiaire->setMessage("Le donneur a validé votre demande de don pour : " . $demande->getDons()->getTitre() . ". Vous pouvez imprimer votre reçu.");
                $notificationBeneficiaire->setUser($demande->getBeneficiaire()); // Notification pour le bénéficiaire
                $notificationBeneficiaire->setDemandeDons($demande);
                $notificationBeneficiaire->setType(Notification::TYPE_DEMANDE_DONS);

                $entityManager->persist($notificationBeneficiaire);
                $entityManager->persist($demande);
            } else {
                return new JsonResponse(['message' => 'Action non valide.'], Response::HTTP_BAD_REQUEST);
            }

            // Sauvegarder les modifications
            $entityManager->flush();

            // Envoyer un e-mail simple au bénéficiaire
            $beneficiaireEmail = $demande->getBeneficiaire()->getEmail();
            $notificationMailer->sendSimpleEmail(
                'amroush123@gmail.com', // Expéditeur
                'wahadayasser25@gmail.com', // Destinataire (e-mail du bénéficiaire)
                'Votre demande de don a été validée', // Sujet de l'e-mail
                sprintf(
                    "Bonjour %s,\n\nVotre demande de don pour '%s' a été validée.\n\nCordialement,\nL’équipe.",
                    $demande->getBeneficiaire()->getNom(),
                    $demande->getDons()->getTitre()
                ) // Corps du message
            );

            return new JsonResponse(['message' => "Demande {$action} avec succès."], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Une erreur est survenue : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    #[Route('/demande/receipt/{id}', name: 'generer_reçu', methods: ['GET'])]
    public function genererReçu(
        DemandeDons $demande,
        PdfGeneratorService $pdfGenerator
    ): Response {
        // Vérifier que la demande est validée
        if ($demande->getStatut() !== 'Validée') {
            throw $this->createAccessDeniedException('La demande doit être validée pour générer un reçu.');
        }

        // Générer le contenu HTML du reçu
        $html = $this->renderView('templates_users/recu/recu.html.twig', [
            'demande' => $demande,
        ]);

        // Générer le PDF et obtenir le chemin du fichier
        $pdfPath = $pdfGenerator->generatePdf($html);

        // Lire le contenu du fichier PDF
        $pdfContent = file_get_contents($pdfPath);

        // Retourner une Response avec le contenu du PDF
        $response = new Response($pdfContent);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'inline; filename="recu_don.pdf"');

        // Supprimer le fichier temporaire après l'avoir envoyé
        unlink($pdfPath);

        return $response;
    }
}
