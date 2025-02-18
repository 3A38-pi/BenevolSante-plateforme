<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class AuthentificationController extends AbstractController
{
    #[Route('/authentification', name: 'app_authentification')]
    public function index(): Response
    {
        return $this->render('authentification/index.html.twig', [
            'controller_name' => 'AuthentificationController',
        ]);
    }


    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hacher le mot de passe
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            // Définir le rôle en fonction du type d'utilisateur
            $typeUtilisateur = $form->get('typeUtilisateur')->getData();
            $role = match ($typeUtilisateur) {
                'donneur' => 'ROLE_DONNEUR',
                'beneficiaire' => 'ROLE_BENEFICIAIRE',
                'professionnel' => 'ROLE_PROFESSIONNEL',
                default => 'ROLE_USER',
            };
            $user->setRoles([$role]);
            $user->setTypeUtilisateur($typeUtilisateur);

            // Sauvegarde en base de données
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Votre compte a été créé avec succès. Veuillez attendre la validation de l\'administrateur.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('authentification/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }



    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils)
    {
        return $this->render('authentification/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),
        ]);
    }
}
