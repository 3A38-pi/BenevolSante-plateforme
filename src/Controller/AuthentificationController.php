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

    #[Route('/profile/edit', name: 'profile_edit')]
    public function editProfile(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        // Ensure the user is logged in
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        // Create a form using a custom form type for profile editing.
        $form = $this->createForm(ProfileEditFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Optionally, check if a new password has been entered.
            // Assuming the form has a 'password' field that may be left empty.
            $plainPassword = $form->get('password')->getData();
            if (!empty($plainPassword)) {
                $user->setPassword(
                    $passwordHasher->hashPassword($user, $plainPassword)
                );
            }
            
            // Save changes to the database.
            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');

            // Redirect to the same page or another page as needed.
            return $this->redirectToRoute('profile_edit');
        }

        return $this->render('profile/edit.html.twig', [
            'editForm' => $form->createView(),
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
}
