<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\UserAuthenticator;
use App\Service\JWTService;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/inscription', name: 'register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        UserAuthenticatorInterface $userAuthenticator,
        UserAuthenticator $authenticator,
        EntityManagerInterface $entityManager,
        SendMailService $mail,
        JWTService $jwt,
        ): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            // On génère le JWT du user
            // on créé le header
            $header = [
                'typ' => 'jwt',
                'alg' => 'HS256',
            ]; 

            // On créé le payload
            $payload = [
                'user_id' => $user->getId(),
            ];

            // On génère le token
            $token =  $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

            // On envoie le mail qui va permettre la validation du compte
            $mail->send(
                'no-reply@monsite.net',
                $user->getEmail(),
                'Activation de votre compte',
                'register',
                [
                    'user' => $user,
                    'token' => $token,
                ]
            );

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verif/{token}', name: 'verify_user')]
    public function verifyUser($token, JWTService $jwt, UserRepository $userRepository, EntityManagerInterface $em) : Response
    {
        // On vérifie si le token est valide, n'a pas été expiré et n'a pas été modifié
        if($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret'))) {
            // On récupère le payload
            $payload = $jwt->getPayload($token);

            // On récupère le user du token
            $user = $userRepository->find($payload['user_id']);

            // On vérifie que l'utilisateur existe et n'a pas encore activé son compte
            if($user && !$user->getIsVerified()) {
                $user->setIsVerified(true);

                $em->persist($user);
                $em->flush();

                $this->addFlash('succes', 'Email validé !');
                return $this->redirectToRoute('profile_index');
            }
        }
        // Ici un pb se pose dans le token
        $this->addFlash('danger', 'L\'email n\'est pas valide ou a expiré');
        return $this->redirectToRoute('login');
    }

    #[Route('/renvoiverif', name: 'resend_verif')]
    public function resendVerif(
        JWTService $jwt,
        SendMailService $mail,
        UserRepository $user,
    ): Response
    {
        $user = $this->getUser();

        if(!$user) {
            $this->addFlash('danger', 'Vous devez être connecté(e) pour accéder à cette page');
            return $this->redirectToRoute('login');
        }

        if($user->getIsVerified()) {
            $this->addFlash('warning', 'Votre mail est déjà valide');
            return $this->redirectToRoute('profile_index');
        }

        // On génère le JWT du user
        // on créé le header
        $header = [
            'typ' => 'jwt',
            'alg' => 'HS256',
        ]; 

        // On créé le payload
        $payload = [
            'user_id' => $user->getId(),
        ];

        // On génère le token
        $token =  $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

        // On envoie le mail qui va permettre la validation du compte
        $mail->send(
            'no-reply@monsite.net',
            $user->getEmail(),
            'Activation de votre compte',
            'register',
            [
                'user' => $user,
                'token' => $token,
            ]
        );

        $this->addFlash('success', 'Mail renvoyé');
        return $this->redirectToRoute('profile_index');

    }

}
