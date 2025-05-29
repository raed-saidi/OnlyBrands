<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Psr\Log\LoggerInterface;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, LoggerInterface $logger): Response
    {
        if ($this->getUser()) {
            $this->addFlash('info', 'You are already logged in.');
            return $this->redirectToRoute('home');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        if ($error) {
            $logger->error('Login error', [
                'error_message' => $error->getMessage(),
                'error_type' => get_class($error),
                'last_username' => $lastUsername,
            ]);

            if ($this->isGoogleUserPasswordError($error)) {
                $this->addFlash('warning', 'This account uses Google authentication. Please use the "Login with Google" button.');
            } else {
                $this->addFlash('error', 'Invalid email or password.');
            }
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    private function isGoogleUserPasswordError($error): bool
    {
        if (!$error) {
            return false;
        }
        return strpos($error->getMessage(), 'Google authentication') !== false;
    }
}