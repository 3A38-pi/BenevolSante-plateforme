<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\UserRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Form\ProfileEditFormType;

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
    public function register(
        Request $request, 
        UserPasswordHasherInterface $passwordHasher, 
        EntityManagerInterface $entityManager
    ): Response {
        // Prevent an admin from registering via this form
        if ($this->getUser() && in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            // If the user is already an admin, redirect or display a message
            return $this->redirectToRoute('app_login');
        }
    
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Hash the password
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
    
            // Set the user role based on the type of user selected
            $typeUtilisateur = $form->get('typeUtilisateur')->getData();
            $role = match ($typeUtilisateur) {
                'donneur' => 'ROLE_DONNEUR',
                'beneficiaire' => 'ROLE_BENEFICIAIRE',
                'professionnel' => 'ROLE_PROFESSIONNEL',
                default => 'ROLE_USER',
            };
            $user->setRoles([$role]);
            $user->setTypeUtilisateur($typeUtilisateur);
    
            // Save to the database
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
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $user = $this->getUser();
    
        if ($user) {
            // Redirect based on the user's role
            if (in_array('ROLE_ADMIN', $user->getRoles())) {
                return $this->redirectToRoute('admin_dashboard'); // Replace with your actual admin dashboard route
            } else {
                return $this->redirectToRoute('home'); // Redirect to /home for non-admin users
            }
        }
    
        return $this->render('authentification/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),
        ]);
    }
    
    // ========= User Management =========
    // This route displays all users in a table.
    #[Route('pages/profile.html', name: 'user_list')]
    public function listUsers(EntityManagerInterface $entityManager): Response
    {
        // Optionally, restrict access to admins only:
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Fetch all users from the database.
        $users = $entityManager->getRepository(User::class)->findAll();

        return $this->render('authentification/user_list.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/user/delete/{id}', name: 'user_delete', methods: ['POST'])]
    public function deleteUser(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // Restrict access to admins only
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            $this->addFlash('error', 'Utilisateur non trouvé.');
            return $this->redirectToRoute('user_list');
        }

        // Validate CSRF token
        if (!$this->isCsrfTokenValid('delete-user-' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('user_list');
        }

        // Remove the user from the database
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        return $this->redirectToRoute('user_list');
    }

    // ========= Page View =========
    // This route shows the logged in user's information.
    #[Route('/pages/pageview.html', name: 'page_view')]
    public function viewProfile(): Response
    {
        // Ensure the user is logged in.
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('profile/view.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/statistics', name: 'statistics')]
    #[IsGranted('ROLE_ADMIN')]
    public function statistics(UserRepository $userRepository): Response
    {
        // Get statistics by user type
        $types = ['donneur', 'beneficiaire', 'professionnel'];
        $statistics = [];
        $totalUsers = 0;

        foreach ($types as $type) {
            $count = $userRepository->countUsersByType($type);
            $statistics[$type] = $count;
            $totalUsers += $count; // Calculate total users
        }

        return $this->render('statistics.html.twig', [
            'labels' => array_keys($statistics),
            'data' => array_values($statistics),
            'totalUsers' => $totalUsers, // Pass total users to the template
        ]);
    }

    #[Route('/profile/edit', name: 'profile_edit')]
    public function editProfile(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser(); // Get the logged-in user
        $form = $this->createForm(ProfileEditFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check if the password fields are filled
            $newPassword = $form->get('password')->getData();
            $confirmPassword = $form->get('confirmPassword')->getData();

            if (!empty($newPassword) && !empty($confirmPassword)) {
                // Validate that the new password and confirm password match
                if ($newPassword !== $confirmPassword) {
                    $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
                    return $this->redirectToRoute('profile_edit');
                }

                // Hash the new password and update the user entity
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);
            }

            // Save the updated user entity to the database
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Profil mis à jour avec succès.');
            return $this->redirectToRoute('profile_edit');
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
