<?php

namespace App\Controller;

use App\Form\ForgotPasswordFormType;
use App\Form\ResetPasswordFormType;
use App\Repository\UserRepository;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/connexion', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Redirige un utilisateur sur une page donnée s'il est déjà connecté
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername, 
            'error' => $error
        ]);
    }

    #[Route(path: '/deconnexion', name: 'logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/oubli-pass', name: 'forgotten_password')]
    public function forgottenPassword(
        Request $request,
        UserRepository $userRepository,
        TokenGeneratorInterface $tokenGeneratorInterface,
        EntityManagerInterface $em,
        SendMailService $mail,
        ): Response
    {
        $form = $this->createForm(ForgotPasswordFormType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            // on va chercher l'utilisateur par son email
            $user = $userRepository->findOneByEmail($form->get('email')->getData());

            // On vérifie si on a un utilisateur
            if ($user) {
                // On génère un token de réinitialisation
                // (on aurait pu utiliser le service token créé)
                $token = $tokenGeneratorInterface->generateToken();
                $user->setResetToken($token);
                $em->persist($user);
                $em->flush();

                // On génère un lien de réinitialisation du mdp
                $url = $this->generateUrl(
                    'reset_password',
                    ['token' => $token],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                // On créé les données du mail
                $context = [
                    'url' => $url,
                    'user' => $user,
                ];

                // Envoi du mail
                $mail->send(
                    'no-reply@commerce.fr', // from
                    $user->getEmail(), // to
                    'Réinitialisation du mot de passe', // titre
                    'password_reset', // le template
                    $context // les données à envoyer
                );

                $this->addFlash('green', 'Email envoyé avec succès');
                return $this->redirectToRoute('login');
            } 

            $this->addFlash('red', 'Un problème est survenu');
            return $this->redirectToRoute('login');
        }

        return $this->render('security/forgot_password.html.twig',
        [
            'forgotPassForm' => $form->createView(),
        ]);
    }

    #[Route(path: '/oubli-pass/{token}', name: 'reset_password')]
    public function resetPassword(
        string $token,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
    ): Response
    {
        // On vérifie si on a ce token dans la BDD
        $user = $userRepository->findOneByResetToken($token);

        if($user) {
            $form = $this->createForm(ResetPasswordFormType::class);

            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                // On efface le token
                $user->setResetToken('');
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData(),
                    )
                );

                $em->persist($user);
                $em->flush();

                $this->addFlash('green', 'Mot de passe réinitialisé');
                return $this->redirectToRoute('login');
            }

            return $this->render('security/reset_password.html.twig',[
                'resetPassForm' => $form->createView(),
            ]);
        }

        $this->addFlash('red', 'Un problème est survenu');
        return $this->redirectToRoute('login');
    }
}
