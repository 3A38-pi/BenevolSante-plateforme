<?php

namespace App\Controller;


use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/event')]
final class EventController extends AbstractController
{
    #[Route(name: 'app_event_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository): Response
    {
        return $this->render('event/index.html.twig', [
            'events' => $eventRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_event_new', methods: ['GET', 'POST'])]
public function new(Request $request, ManagerRegistry $mr): Response
{
    $imageDir = $this->getParameter('Event_dir'); // Récupération du répertoire d'images défini dans config/services.yaml

    $event = new Event();
    
    // Créer le formulaire
    $form = $this->createForm(EventType::class, $event);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Gestion de l'image
        if ($imageFile = $form['image']->getData()) {
            // Vérifier que l'image a bien été téléchargée
            if ($imageFile instanceof UploadedFile) {
                // Générer un nom unique pour le fichier
                $fileName = uniqid() . '.' . $imageFile->guessExtension();
                
                // Vérifier que le dossier de destination existe
                if (!is_dir($imageDir)) {
                    mkdir($imageDir, 0777, true); // Créer le dossier si nécessaire
                }
                
                // Déplacer l'image vers le dossier cible
                try {
                    $imageFile->move($imageDir, $fileName);
                    // Assigner le nom du fichier à l'entité Event
                    $event->setImage($fileName);
                } catch (FileException $e) {
                    // Gestion des erreurs d'upload de fichier
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                    return $this->redirectToRoute('app_event_new');
                }
            }
        }

        // Sauvegarder les données avec Doctrine
        $em = $mr->getManager();
        $em->persist($event);
        $em->flush();

        // Message de succès
        $this->addFlash('success', 'L\'événement a été ajouté avec succès.');

        // Rediriger vers la liste des événements
        return $this->redirectToRoute('app_event_index');
    }

    // Afficher le formulaire dans la vue
    return $this->render('event/new.html.twig', [
        'form' => $form->createView(),
    ]);
}

#[Route('/{id}', name: 'app_event_show', methods: ['GET'])]
public function show(Event $event): Response
{
    return $this->render('event/show.html.twig', [
        'event' => $event,
    ]);
}

   

    #[Route('/{id}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $event->getId(), $request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }
    
}
