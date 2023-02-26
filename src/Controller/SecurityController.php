<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantProfileFormType;
use App\Form\RegistrationFormType;
use App\Form\ResetPassWordFormType;
use App\Form\ResetPassWordRequestFormType;
use App\Repository\ParticipantRepository;
use App\Security\UserAuthenticator;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use http\Url;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'security_login', methods:['GET','POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si problème lors du login renvoie sur l'exception
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'security_logout', methods:['GET'])]
    public function logout(): void
    {}

    #[Route(path: '/profile', name: 'security_profile', methods:['GET'])]
    public function profile(): Response
    {
        $user = $this->getUser();

        return $this->render('user/profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/updateProfile', name: 'security_updateProfile', methods: ['GET', 'POST'])]
    public function updateProfile(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, UserAuthenticator $authenticator, UserAuthenticatorInterface $userAuthenticator): Response
    {

        $user = $this->getUser();

        //Création du formulaire
        $form = $this->createForm(ParticipantProfileFormType::class, $user);
        $form->handleRequest($request);

        //Vérification du formulaire
        if ($form->isSubmitted() && $form->isValid()) {

            //Modification du Participant en BDD (Base de donnée)
            $entityManager->flush();

            //Message de confirmation
            $this->addFlash('succes', 'Le profil a bien été modifié !');

            //Connexion automatique du participant
            return $this->redirectToRoute('security_profile');
        }

        return $this->render('user/updateProfile.html.twig', [
            'UpdateProfilForm' => $form->createView(),
        ]);
    }

    #[Route(path: '/profile/delete', name: 'deleteProfile', methods:['GET'])]
    public function deleteUser(EntityManagerInterface $entityManager, UserAuthenticatorInterface $userAuthenticator): Response
    {
        $user = $this->getUser();
        if (!$user)
        {
            return $this->redirectToRoute('app_login');
        }
        $this->container->get('security.token_storage')->setToken(null);
        $this->logout();

        // Suppression de l'image de profil de l'utilisateur, s'il en a une
        $imageProfil = $user->getImageParticipant();
        if ($imageProfil) {
            $imagePath = $this->getParameter('participant_image_directory') . '/' . $imageProfil;
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $entityManager->remove($user);
        $entityManager->flush();
        // $this->get('session')->remove('user');
        /* pour tout supprimer */
        // $this->get('session')->clear();

        return $this->render('home/home.html.twig');
    }
    #[Route(path: '/forgetPassword', name: 'security_forgettenPassword', methods:['GET','POST'])]
    public function forgetPassword(Request $request,
                                   ParticipantRepository $participantRepository,
                                   TokenGeneratorInterface $tokenGenerator,
                                   EntityManagerInterface $entityManager,
                                   SendMailService $mail
    ): Response
    {
        $form = $this->createForm(ResetPassWordRequestFormType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $criteria = ['email' => $form->get('email')->getData()];
            $user = $participantRepository->findOneBy($criteria);
// on verifie si on a un utilisateur

            if ($user){
                //on génére un token de reinitialisation
                $token = $tokenGenerator->generateToken();
                $user->setResetToken($token);
                $entityManager->persist($user);
                $entityManager->flush();

                //on génére un lien de reinitialisation du mot de passe
                $url = $this->generateUrl('reset_pass',['token' => $token],
                UrlGeneratorInterface::ABSOLUTE_URL);

                //on crée les données du mail
                $context=compact('url','user');

                //Envoi du mail
                $mail->send('453f7e6784-9562de@inbox.mailtrap.io',
                $user->getEmail(),
                'Réinitialisation du mot de passe',
                'password_reset',
                $context);
                $this->addFlash('success','E-mail envoyé');
                dd($url);
                return $this->redirectToRoute('security_login');



            }
            $this->addFlash('danger', 'un problème est survenu');
            return $this->redirectToRoute('security_login');
        }

        return $this->render('security/reset_password_request.html.twig',[
            'requestPassForm' => $form->createView()
        ]);
    }

    #[Route(path: '/forgetPassword/{token}', name: 'reset_pass', methods:['GET','POST'])]
    public function resetPass(string $token,
    Request $request,
    ParticipantRepository $participantRepository,
    EntityManagerInterface $entityManager,UserPasswordHasherInterface $userPasswordHasher)
    :Response
    {
        //on vérifie si on a le token enregistré
        $user = $entityManager->getRepository(Participant::class)->findOneBy(['resetToken' => $token]);

        if($user){
            $form = $this->createForm(ResetPassWordFormType::class);

            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                //on efface le token
                $user->setResetToken('');
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success','mot de passe modifier');
                return $this->redirectToRoute('security_login');
            }

            return $this->render('security/reset_password.html.twig',[
                'PassForm' => $form->createView()
            ]);
        }
        $this->addFlash('danger','jeton invalide');
        return $this->redirectToRoute('security_login');

    }
}
