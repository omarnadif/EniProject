<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantProfileFormType;
use App\Security\UserAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

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
    public function updateProfile(Participant $participant = null, Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, UserAuthenticator $authenticator, UserAuthenticatorInterface $userAuthenticator, SluggerInterface $slugger): Response
    {
        // Récupération de l'utilisateur courant
        $user = $this->getUser();

        // Création du formulaire en utilisant le form ParticipantProfileFormType
        $form = $this->createForm(ParticipantProfileFormType::class, $user);

        // Validation du formulaire si soumis
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // Récupération des données du formulaire
            $userData = $form->getData();

            // Récupération de l'image de profil du formulaire
            $imageProfil = $form->get('photoProfil')->getData();

            // Vérification si une image de profil a été téléchargée
            if ($imageProfil) {

                // Génération d'un nom de fichier unique pour éviter les conflits
                $originalFilename = pathinfo($imageProfil->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageProfil->guessExtension();

                // Déplacement de l'image téléchargée dans le répertoire de stockage défini dans service.yaml (dans participant_image_directory)
                try {
                    $imageProfil->move(
                        $this->getParameter('participant_image_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Gestion d'une exception si nécessaire
                }

                // Ajout du nom du fichier d'image dans l'entité Participant
                if ($user instanceof Participant) {
                    $user->setImageParticipant($newFilename);
                }
            }

            // Mise à jour du mot de passe si soumis
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $userPasswordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            // Enregistrement des modifications de l'entité Participant en BDD
            $entityManager->flush();

            // Message de confirmation
            $this->addFlash('succes', 'Le profil a bien été modifié !');

            // Redirection vers la page de profil de l'utilisateur
            return $this->redirectToRoute('security_profile');
        }

        // Affichage de la page de modification de profil avec le formulaire
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
}
