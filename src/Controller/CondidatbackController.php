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
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mime\Email;


use Symfony\Component\Mailer\Mailer;




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

    #[Route('/{id}/accepter', name: 'app_condidatback_accepter', methods: ['POST'])]
    public function accepter(Condidat $condidat, EntityManagerInterface $entityManager): Response

{
    // Sauvegarde en base de données
    $entityManager->flush();

    // Configuration du transport SMTP avec Gmail
    $transport = Transport::fromDsn('smtp://amroush123@gmail.com:npcfowmbtolgyqfe@smtp.gmail.com:587');
    $mailer = new Mailer($transport);

    // Récupération de l'email du candidat
    $recipientEmail = $condidat->getEmail();
    
    // Création et envoi de l'email
    $email = (new Email())
        ->from('amroush123@gmail.com')
        ->to($recipientEmail) // Utilisation de l'email du candidat
        ->subject('Notification : Candidature acceptée')
        ->text(sprintf(
            "Bonjour %s,\n\nVotre candidature a été acceptée ! 🎉\n\nCordialement,\nL’équipe.",
            $condidat->getNom()
        ));

    $mailer->send($email);

    // Retourner une réponse JSON
    return $this->redirectToRoute('app_condidatback_felicitation');

}
#[Route('/condidatback/felicitation', name: 'app_condidatback_felicitation', methods: ['GET'])]
public function felicitation(): Response
{
    return $this->render('condidatback/felicitaion.html.twig');
}

}
