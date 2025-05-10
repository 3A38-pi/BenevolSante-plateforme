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
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\Persistence\ManagerRegistry;

#[Route('/event')]
final class EventController extends AbstractController
{
    #[Route('/', name: 'app_event_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository, Request $request): Response
    {
        // Récupérer le terme de recherche
        $searchTerm = $request->query->get('q');
        $queryBuilder = $eventRepository->createQueryBuilder('e');
    
        // Si un terme de recherche est fourni, filtrer les résultats
        if ($searchTerm) {
            $queryBuilder
                ->where('LOWER(e.nom) LIKE LOWER(:searchTerm) OR LOWER(e.description) LIKE LOWER(:searchTerm)')
                ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }
    
        // Trier les résultats par nom
        $queryBuilder->orderBy('e.nom', 'ASC');
    
        // Exécuter la requête et récupérer les résultats
        $events = $queryBuilder->getQuery()->getResult();
    
        // Rendre la vue avec les événements et le terme de recherche
        return $this->render('event/index.html.twig', [
            'events' => $events,
            'query' => $searchTerm // Passer le terme de recherche à la vue
        ]);
    }
    
    #[Route('/statistiques', name: 'app_event_statistiques', methods: ['GET'])]
    public function statistiques(EntityManagerInterface $em): Response
    {
        // Correct the query to match the expected key names
        $statsCategorie = $em->getRepository(Event::class)
            ->createQueryBuilder('e')
            ->select('c.type AS categorieType, COUNT(e.id) AS count')  // Using 'categorieType' and 'count' as keys
            ->join('e.categorie', 'c')  // Join with Categorie
            ->groupBy('c.id')  // Group by category ID
            ->getQuery()
            ->getResult();

        // Render the statistics page with the data
        return $this->render('event/statistique.html.twig', [
            'statsCategorie' => $statsCategorie,  // Pass the statistics to the view
        ]);
    }

    #[Route('/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ManagerRegistry $mr): Response
    {
        $imageDir = $this->getParameter('Event_dir');

        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($imageFile = $form['image']->getData()) {
                if ($imageFile instanceof UploadedFile) {
                    $fileName = uniqid() . '.' . $imageFile->guessExtension();
                    
                    if (!is_dir($imageDir)) {
                        mkdir($imageDir, 0777, true);
                    }

                    try {
                        $imageFile->move($imageDir, $fileName);
                        $event->setImage($fileName);
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                        return $this->redirectToRoute('app_event_new');
                    }
                }
            }

            $em = $mr->getManager();
            $em->persist($event);
            $em->flush();

            $this->addFlash('success', 'L\'événement a été ajouté avec succès.');
            return $this->redirectToRoute('app_event_index');
        }

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
            foreach ($event->getParticipants() as $participant) {
                $entityManager->remove($participant);
            }

            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }
}
