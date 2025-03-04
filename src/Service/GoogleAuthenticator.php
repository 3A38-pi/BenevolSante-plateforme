<?php
namespace App\Service;

use KnpU\OAuth2ClientBundle\Client\Provider\GoogleClient;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class GoogleAuthenticator extends AbstractAuthenticator
{
    private GoogleClient $client;
    private RouterInterface $router;
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;

    public function __construct(
        GoogleClient $client, 
        RouterInterface $router, 
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        ParameterBagInterface $params
    ) {
        $this->client = $client;
        $this->router = $router;
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): Passport
    {
        $accessToken = $this->client->getAccessToken();
        $googleUser = $this->client->fetchUserFromToken($accessToken);
        $email = $googleUser->getEmail();
    
        return new SelfValidatingPassport(
            new UserBadge($email, function () use ($googleUser) {
                // Check if the user already exists in the database
                $user = $this->userRepository->findOneBy(['email' => $googleUser->getEmail()]);
    
                if (!$user) {
                    // If the user doesn't exist, create a new one
                    $user = new User();
                    $user->setGoogleId($googleUser->getId());
                    $user->setEmail($googleUser->getEmail());
    
                    // Split the full name into first name and last name
                    $fullName = $googleUser->getName();
                    $nameParts = explode(' ', $fullName, 2); // Split into at most 2 parts
                    $prenom = $nameParts[0] ?? ''; // First name
                    $nom = $nameParts[1] ?? ''; // Last name (if available)
    
                    $user->setNom($nom);
                    $user->setPrenom($prenom);
    
                    // Generate a random CIN
                    
                    // Generate a random password
                    $randomPassword = bin2hex(random_bytes(8)); // Generates a random 16-character password
                    //$hashedPassword = $this->passwordHasher->hashPassword($user, $randomPassword);
                    $user->setPassword($randomPassword);
    
                    // Set a default phone number
    
                    // Set a default role
                    $user->setRoles(['ROLE_DONNEUR']);
                    $user->setTypeUtilisateur('donneur');
    
                   
    
                    // Download Google profile picture and save it as the user's photo
                    /*$avatar = $googleUser->getAvatar();
                    if ($avatar) {
                        $newFilename = uniqid() . '.jpg';
                        $imageData = file_get_contents($avatar);
                        file_put_contents($this->uploadsDirectory . '/' . $newFilename, $imageData);
                        $user->setPhoto($newFilename);
                    }*/
    
                    // Persist the new user in the database
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();
                    
                }
    
                return $user;
            })
        );
    }
    public function onAuthenticationSuccess(Request $request, $token, string $firewallName): ?RedirectResponse
    {
        // Get the authenticated user
        $user = $token->getUser();

        $request->getSession()->set('user_id', $user->getId());

        return new RedirectResponse($this->router->generate('home'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?RedirectResponse
    {
        // Add a flash message to inform the user about the failure
       // $request->getSession()->getFlashBag()->add('error', 'Google authentication failed. Please try again.');

        // Redirect to the login page
        return new RedirectResponse($this->router->generate('app_user_login'));
    }
}