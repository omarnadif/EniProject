<?php

namespace App\Controller;

use App\Form\ParticipantProfileFormType;
use App\Security\UserAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
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

            //Récupération des données du formulaire
            $userData = $form->getData();

            //Mise à jour du mot de passe, s'il a été modifié
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $userPasswordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

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

    #[Route(path: '/profile/delete', name: 'security_deleteProfile', methods:['GET'])]
    public function deleteUser(EntityManagerInterface $entityManager, UserAuthenticatorInterface $userAuthenticator): Response
    {
        $user = $this->getUser();
        if (!$user)
        {
            return $this->redirectToRoute('security_login');
        }
        $this->container->get('security.token_storage')->setToken(null);
        $this->logout();

        $entityManager->remove($user);
        $entityManager->flush();
       // $this->get('session')->remove('user');
        /* pour tout supprimer */
       // $this->get('session')->clear();

                return $this->render('home/home.html.twig');
    }
}
