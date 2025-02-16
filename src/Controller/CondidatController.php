<?php

namespace App\Controller;

use App\Entity\Condidat;
use App\Form\CondidatType;
use App\Repository\CondidatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/condidat')]
final class CondidatController extends AbstractController{
    #[Route(name: 'app_condidat_index', methods: ['GET'])]
    public function index(CondidatRepository $condidatRepository): Response
    {
        return $this->render('condidat/index.html.twig', [
            'condidats' => $condidatRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_condidat_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $condidat = new Condidat();
        $form = $this->createForm(CondidatType::class, $condidat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($condidat);
            $entityManager->flush();

            return $this->redirectToRoute('app_condidat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('condidat/new.html.twig', [
            'condidat' => $condidat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_condidat_show', methods: ['GET'])]
    public function show(Condidat $condidat): Response
    {
        return $this->render('condidat/show.html.twig', [
            'condidat' => $condidat,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_condidat_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Condidat $condidat, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CondidatType::class, $condidat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_condidat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('condidat/edit.html.twig', [
            'condidat' => $condidat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_condidat_delete', methods: ['POST'])]
    public function delete(Request $request, Condidat $condidat, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$condidat->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($condidat);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_condidat_index', [], Response::HTTP_SEE_OTHER);
    }
}
