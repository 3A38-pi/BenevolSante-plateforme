<?php

namespace App\Controller;

use App\Entity\Offre;
use App\Form\Offre1Type;
use App\Repository\OffreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/offreback')]
final class OffrebackController extends AbstractController{
    #[Route(name: 'app_offreback_index', methods: ['GET'])]
    public function index(OffreRepository $offreRepository): Response
    {
        return $this->render('offreback/index.html.twig', [
            'offres' => $offreRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_offreback_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $offre = new Offre();
        $form = $this->createForm(Offre1Type::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($offre);
            $entityManager->flush();

            return $this->redirectToRoute('app_offreback_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('offreback/new.html.twig', [
            'offre' => $offre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_offreback_show', methods: ['GET'])]
    public function show(Offre $offre): Response
    {
        return $this->render('offreback/show.html.twig', [
            'offre' => $offre,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_offreback_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Offre $offre, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Offre1Type::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_offreback_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('offreback/edit.html.twig', [
            'offre' => $offre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_offreback_delete', methods: ['POST'])]
    public function delete(Request $request, Offre $offre, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$offre->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($offre);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_offreback_index', [], Response::HTTP_SEE_OTHER);
    }
}
