<?php

// src/Controller/EvaluationController.php

namespace App\Controller;
use App\Entity\ResponseEvaluation;
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
use App\Service\TwilioService;

use Symfony\Component\HttpFoundation\JsonResponse;

use App\Entity\Question;



class EvaluationController extends AbstractController
{#[Route('/add', name: 'create_evaluation')]
public function create(Request $request, EntityManagerInterface $em, TwilioService $twilioService): Response
{
    // Créer une nouvelle évaluation
    $evaluation = new Evaluation();
    
    // Créer le formulaire lié à l'entité
    $form = $this->createForm(EvaluationType::class, $evaluation);
    
    // Traiter la soumission du formulaire
    $form->handleRequest($request);
    
    if ($form->isSubmitted() && $form->isValid()) {
        // Associer les questions à l'évaluation
        foreach ($evaluation->getQuestions() as $question) {
            $question->setEvaluation($evaluation);
            $em->persist($question);
        }

        // Persister l'évaluation
        $em->persist($evaluation);
        $em->flush();
    
        // Envoyer un SMS avec Twilio
        $message = "Nouvelle évaluation créée : " . $evaluation->getName();
        $twilioService->sendSms('+21651138972', $message);  // Remplacez par le numéro du destinataire
    
        // Message de confirmation
        $this->addFlash('success', 'Évaluation créée avec succès ! Un SMS a été envoyé.');

        // Redirection vers la liste des évaluations
        return $this->redirectToRoute('evaluations_with_responses');
    }
    
    // Affichage du formulaire
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
    #[Route('/save-response', name: 'save_response', methods: ['POST'])]
    public function saveResponse(Request $request, EntityManagerInterface $entityManager)
    {
        // Récupérer les données JSON envoyées
        $data = json_decode($request->getContent(), true);

        // Vérification des données
        if (!isset($data['question_id'], $data['answer'])) {
            return new JsonResponse(['success' => false, 'error' => 'Données manquantes'], 400);
        }

        // Trouver la question correspondante
        $question = $entityManager->getRepository(Question::class)->find($data['question_id']);
        if (!$question) {
            return new JsonResponse(['success' => false, 'error' => 'Question non trouvée'], 404);
        }

        // Créer une nouvelle réponse d'évaluation
        $responseEvaluation = new ResponseEvaluation(); // Corrected to ResponseEvaluation
        $responseEvaluation->setAnswer($data['answer']);
        $responseEvaluation->setQuestion($question);

        // Si la question n'est pas ouverte (type "Vrai" ou "Faux"), on ajoute la note
        if (isset($data['rating'])) {
            $responseEvaluation->setRating($data['rating']);
        }

        // Sauvegarder la réponse
        $entityManager->persist($responseEvaluation);
        $entityManager->flush();

        // Retourner la réponse JSON
        return new JsonResponse(['success' => true]);
    }
}

