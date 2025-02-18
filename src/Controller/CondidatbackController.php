<?php

namespace App\Controller;

use App\Entity\Condidat;
use App\Form\Condidat1Type;
use App\Repository\CondidatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/condidatback')]
final class CondidatbackController extends AbstractController{
    #[Route(name: 'app_condidatback_index', methods: ['GET'])]
    public function index(CondidatRepository $condidatRepository): Response
    {
        return $this->render('condidatback/index.html.twig', [
            'condidats' => $condidatRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_condidatback_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $condidat = new Condidat();
        $form = $this->createForm(Condidat1Type::class, $condidat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($condidat);
            $entityManager->flush();

            return $this->redirectToRoute('app_condidatback_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('condidatback/new.html.twig', [
            'condidat' => $condidat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_condidatback_show', methods: ['GET'])]
    public function show(Condidat $condidat): Response
    {
        return $this->render('condidatback/show.html.twig', [
            'condidat' => $condidat,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_condidatback_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Condidat $condidat, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Condidat1Type::class, $condidat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_condidatback_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('condidatback/edit.html.twig', [
            'condidat' => $condidat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_condidatback_delete', methods: ['POST'])]
    public function delete(Request $request, Condidat $condidat, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$condidat->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($condidat);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_condidatback_index', [], Response::HTTP_SEE_OTHER);
    }
}
