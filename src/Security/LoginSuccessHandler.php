<?php
namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        // Récupérer le rôle de l'utilisateur connecté
        $roles = $token->getRoleNames();

        // Si l'utilisateur a le rôle ROLE_ADMIN, rediriger vers le dashboard admin
        if (in_array('ROLE_ADMIN', $roles)) {
            return new RedirectResponse($this->router->generate('admin_dashboard'));
        }

        // Sinon, rediriger vers la page home
        return new RedirectResponse($this->router->generate('home'));
    }
}
