<?php

// src/Controller/EvaluationController.php

namespace App\Controller;

use App\Entity\Evaluation;
use App\Form\EvaluationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\EvaluationRepository;
use App\Entity\EvaluationResponse;
use App\Form\EvaluationResponseType;
use Doctrine\Persistence\ManagerRegistry;

use App\Repository\EvaluationResponseRepository;


class EvaluationController extends AbstractController
{#[Route('/add', name: 'create_evaluation')]
public function create(Request $request, EntityManagerInterface $em): Response
{
    // Créer une nouvelle instance de l'entité Evaluation
    $evaluation = new Evaluation();
    
    // Créer le formulaire avec l'entité Evaluation
    $form = $this->createForm(EvaluationType::class, $evaluation);
    
    // Traiter la soumission du formulaire
    $form->handleRequest($request);
    
    if ($form->isSubmitted() && $form->isValid()) {
        // Récupérer les options du formulaire, les convertir en tableau et nettoyer les espaces
        $options = explode(',', $form->get('options')->getData());  // Conversion de la chaîne en tableau
        $evaluation->setOptions(array_map('trim', $options));  // Enlever les espaces superflus autour des options

        // Si le champ 'createdAt' n'est pas déjà défini, on le crée manuellement
        if (!$evaluation->getCreatedAt()) {
            $evaluation->setCreatedAt(new \DateTime()); // Attribuer la date de création
        }

        // Persist l'entité dans la base de données
        $em->persist($evaluation);
        $em->flush();
    
        // Ajouter un message flash pour indiquer que l'évaluation a été créée
        $this->addFlash('success', 'Évaluation créée avec succès !');
    
        // Rediriger l'utilisateur vers la même page ou une autre page après la création
        return $this->redirectToRoute('create_evaluation');
    }
    
    // Rendu du formulaire dans la vue
    return $this->render('evaluation/index.html.twig', [
        'form' => $form->createView(),
    ]);
}


    
    #[Route('/list', name: 'list_evaluations')]
    public function listEvaluations(EvaluationRepository $repo): Response
    {
        // Récupérer toutes les évaluations
        $evaluations = $repo->findAll();

        return $this->render('evaluation/Front.html.twig', [
            'evaluations' => $evaluations,
        ]);
    }

    // Afficher une évaluation spécifique et permettre à l'utilisateur de répondre
    #[Route('/list/{id}', name: 'show_evaluation')]
    public function showEvaluation(Evaluation $evaluation, Request $request, EntityManagerInterface $em): Response
    {
        // Créer une nouvelle réponse pour l'évaluation
        $response = new EvaluationResponse();
        $response->setEvaluation($evaluation);

        // Créer le formulaire pour la réponse
        $form = $this->createForm(EvaluationResponseType::class, $response);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrer la réponse dans la base de données
            $em->persist($response);
            $em->flush();

            // Afficher un message de succès
            $this->addFlash('success', 'Votre réponse a été enregistrée avec succès !');

            // Rediriger vers la page des évaluations après soumission
            return $this->redirectToRoute('list_evaluations');
        }

        // Afficher l'évaluation et le formulaire de réponse
        return $this->render('evaluation/show.html.twig', [
            'evaluation' => $evaluation,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/ev', name: 'evaluations_with_responses')]
    public function evaluationsWithResponses(EvaluationRepository $repo): Response
    {
        $evaluations = $repo->findAll();
    
        return $this->render('evaluation/list.html.twig', [
            'evaluations' => $evaluations,
        ]);
    }
    #[Route('/update/{id}', name: 'update_evaluation')]
public function updateEvaluation(int $id, Request $request, EvaluationRepository $repo, ManagerRegistry $doctrine): Response
{
    $em = $doctrine->getManager();
    $evaluation = $repo->find($id);

    if (!$evaluation) {
        throw $this->createNotFoundException('Évaluation non trouvée');
    }

    $form = $this->createForm(EvaluationType::class, $evaluation);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->persist($evaluation);
        $em->flush();

        $this->addFlash('success', 'Évaluation mise à jour avec succès !');

        // Rediriger vers la page précédente
        $previousUrl = $request->headers->get('referer');
        if ($previousUrl) {
            return $this->redirect($previousUrl);
        }

        return $this->redirectToRoute('list_evaluations'); // Redirection par défaut
    }

    return $this->render('evaluation/update.html.twig', [
        'form' => $form->createView(),
    ]);
}

    #[Route("/remove/{id}", name: "remove")]
    public function remove(ManagerRegistry $doctrine, int $id, Request $request): Response
    {
        $entityManager = $doctrine->getManager();
        $evaluation = $entityManager->getRepository(Evaluation::class)->find($id);
    
        if (!$evaluation) {
            throw $this->createNotFoundException('Aucune évaluation trouvée pour l’ID ' . $id);
        }
    
        $entityManager->remove($evaluation);
        $entityManager->flush();
    
        $this->addFlash('success', 'Évaluation supprimée avec succès !');
    
        // Récupérer l'URL précédente
        $previousUrl = $request->headers->get('referer');
        if ($previousUrl) {
            return $this->redirect($previousUrl); // Retourner à la page précédente
        }
    
        return $this->redirectToRoute('list_evaluations'); // Si l'URL précédente est introuvable
    }
    
}