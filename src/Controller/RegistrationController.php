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
use Psr\Log\LoggerInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ): Response {
        if ($this->getUser()) {
            $this->addFlash('info', 'You are already logged in.');
            return $this->redirectToRoute('home');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            // Log form data for debugging
            $logger->debug('Registration form submitted', [
                'email' => $user->getEmail(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'has_password' => !empty($plainPassword),
                'password_length' => strlen($plainPassword ?? ''),
                'password_start' => substr($plainPassword ?? '', 0, 3) . '...',
            ]);

            if (empty($plainPassword)) {
                $this->addFlash('error', 'Password cannot be empty.');
                $logger->error('Empty password submitted', ['email' => $user->getEmail()]);
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }

            // Check for duplicate email
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
            if ($existingUser) {
                $this->addFlash('error', 'An account with this email already exists.');
                $logger->error('Duplicate email during registration', [
                    'email' => $user->getEmail(),
                    'existing_is_google_user' => $existingUser->isGoogleUser(),
                ]);
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }

            // Hash and set password
            $hashedPassword = $userPasswordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
            $user->setIsGoogleUser(false);

            $logger->debug('Password hashed', [
                'email' => $user->getEmail(),
                'hash_start' => substr($hashedPassword, 0, 10) . '...',
            ]);

            try {
                $entityManager->persist($user);
                $entityManager->flush();

                // Verify stored password
                $savedUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
                if (!$savedUser) {
                    throw new \Exception('User not found after save');
                }

                $isPasswordValid = $userPasswordHasher->isPasswordValid($savedUser, $plainPassword);
                $storedHash = $savedUser->getPassword();

                $logger->info('User registered', [
                    'email' => $savedUser->getEmail(),
                    'is_google_user' => $savedUser->isGoogleUser(),
                    'password_valid' => $isPasswordValid,
                    'stored_hash' => substr($storedHash, 0, 10) . '...',
                ]);

                if (!$isPasswordValid || $storedHash === '$2y$13$7nxmMIDK7n2YKbj4AZBMPeYX9nI9R.ttgyq6/brdj7G50na7HVS5K') {
                    $logger->error('Stored password mismatch or matches known random hash', [
                        'email' => $savedUser->getEmail(),
                        'stored_hash' => $storedHash,
                    ]);
                    $entityManager->remove($savedUser);
                    $entityManager->flush();
                    $this->addFlash('error', 'Password storage failed. Please try again.');
                    return $this->render('registration/register.html.twig', [
                        'registrationForm' => $form->createView(),
                    ]);
                }

                $this->addFlash('success', 'Account created! Please log in.');
                return $this->redirectToRoute('app_login');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error creating account: ' . $e->getMessage());
                $logger->error('Registration error', [
                    'email' => $user->getEmail(),
                    'error' => $e->getMessage(),
                    'exception_trace' => $e->getTraceAsString(),
                ]);
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}