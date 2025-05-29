<?php
namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Psr\Log\LoggerInterface;

class GoogleAuthenticator extends OAuth2Authenticator implements AuthenticationEntryPointInterface
{
    private $clientRegistry;
    private $entityManager;
    private $router;
    private $userRepository;
    private $passwordHasher;
    private $logger;

    public function __construct(
        ClientRegistry $clientRegistry,
        EntityManagerInterface $entityManager,
        RouterInterface $router,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        LoggerInterface $logger
    ) {
        $this->clientRegistry = $clientRegistry;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->logger = $logger;
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('google');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function() use ($accessToken, $client) {
                /** @var GoogleUser $googleUser */
                $googleUser = $client->fetchUserFromToken($accessToken);
                $email = $googleUser->getEmail();

                $this->logger->info('Google authentication attempt', ['email' => $email]);

                $existingUser = $this->userRepository->findOneBy(['email' => $email]);

                if ($existingUser) {
                    $this->logger->info('Existing user found', [
                        'email' => $email,
                        'is_google_user' => $existingUser->isGoogleUser(),
                        'current_password_hash' => substr($existingUser->getPassword(), 0, 10) . '...',
                    ]);

                    // Only update isGoogleUser, preserve existing password
                    if (!$existingUser->isGoogleUser()) {
                        $existingUser->setIsGoogleUser(true);
                        $this->entityManager->flush();
                        $this->logger->info('Updated user to Google user', ['email' => $email]);
                    }
                    return $existingUser;
                }

                $this->logger->info('Creating new Google user', ['email' => $email]);

                $user = new User();
                $user->setEmail($email);
                $user->setFirstName($googleUser->getFirstName() ?? 'Google');
                $user->setLastName($googleUser->getLastName() ?? 'User');
                $user->setIsGoogleUser(true);

                $randomPassword = bin2hex(random_bytes(16));
                $hashedPassword = $this->passwordHasher->hashPassword($user, $randomPassword);
                $user->setPassword($hashedPassword);

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $this->logger->info('New Google user created', [
                    'email' => $email,
                    'password_hash' => substr($hashedPassword, 0, 10) . '...',
                ]);

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $this->logger->info('Google authentication successful', ['email' => $token->getUserIdentifier()]);
        return new RedirectResponse($this->router->generate('home'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $this->logger->error('Google authentication failed', [
            'error' => $exception->getMessage(),
            'exception_trace' => $exception->getTraceAsString(),
        ]);
        return new Response(strtr($exceptionUsd::getMessageKey(), $exception->getMessageData()), Response::HTTP_FORBIDDEN);
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new RedirectResponse('/login', Response::HTTP_TEMPORARY_REDIRECT);
    }
}