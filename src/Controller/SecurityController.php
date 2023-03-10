<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantProfileFormType;
use App\Form\ResetPassWordFormType;
use App\Form\ResetPassWordRequestFormType;
use App\Repository\ParticipantRepository;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\String\Slugger\SluggerInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'security_login', methods:['GET','POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Récupération de l'éventuelle erreur d'authentification
        $error = $authenticationUtils->getLastAuthenticationError();

        // Récupération du dernier nom d'utilisateur entré par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        // Rendu de la vue de login avec les variables récupérées
        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'security_logout', methods:['GET'])]
    public function logout(): void
    {}

    #[Route(path: '/affichageProfil', name: 'security_profil', methods:['GET'])]
    public function affichageProfil(): Response
    {
        // Récupération de l'utilisateur connecté
        $user = $this->getUser();

        // Renvoie de la vue du profil avec les données de l'utilisateur
        return $this->render('user/profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/updateProfile', name: 'security_updateProfile', methods: ['GET', 'POST'])]
    public function updateProfile(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, SluggerInterface $slugger, Filesystem $filesystem): Response
    {
        // Récupération de l'utilisateur courant
        $user = $this->getUser();

        // Création du formulaire en utilisant le form ParticipantProfileFormType
        $form = $this->createForm(ParticipantProfileFormType::class, $user);

        // Validation du formulaire si soumis
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // Récupération des données du formulaire
            $user = $form->getData();

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

                // Suppression de l'ancienne image s'il en existe une
                $oldFilename = $user->getImageParticipant();
                if ($oldFilename) {
                    $filesystem->remove($this->getParameter('participant_image_directory').'/'.$oldFilename);
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
            return $this->redirectToRoute('security_profil');
        }

        // Affichage de la page de modification de profil avec le formulaire
        return $this->render('user/updateProfile.html.twig', [
            'UpdateProfilForm' => $form->createView(),
        ]);
    }

    #[Route(path: '/profile/delete', name: 'security_deleteProfile', methods:['GET'])]
    public function deleteUser(EntityManagerInterface $entityManager): Response
    {
        // Récupération de l'utilisateur actuel
        $user = $this->getUser();

        // Si l'utilisateur n'est pas connecté, redirection vers la page de login
        if (!$user)
        {
            return $this->redirectToRoute('security_login');
        }

        // Suppression du token de l'utilisateur actuel
        $this->container->get('security.token_storage')->setToken(null);

        // Déconnexion de l'utilisateur actuel
        $this->logout();

        // Suppression de l'image de profil de l'utilisateur, s'il en a une
        $imageProfil = $user->getImageParticipant();
        if ($imageProfil) {
            $imagePath = $this->getParameter('participant_image_directory') . '/' . $imageProfil;
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        // Suppression de l'utilisateur en BDD (Base de données)
        $entityManager->remove($user);
        $entityManager->flush();
        // $this->get('session')->remove('user');
        /* pour tout supprimer */
        // $this->get('session')->clear();

        // Redirection vers la page d'accueil
        return $this->render('home/home.html.twig');
    }

    #[Route(path: '/forgetPassword', name: 'security_forgettenPassword', methods:['GET','POST'])]
    public function forgetPassword(Request $request, ParticipantRepository $participantRepository, TokenGeneratorInterface $tokenGenerator, EntityManagerInterface $entityManager, SendMailService $mail): Response
    {
        // Création du formulaire de demande de réinitialisation de mot de passe
        $form = $this->createForm(ResetPassWordRequestFormType::class);

        // Traitement du formulaire
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if($form->isSubmitted() && $form->isValid()){

            // Récupération de l'utilisateur en utilisant l'adresse e-mail fournie dans le formulaire
            $criteria = ['email' => $form->get('email')->getData()];
            $user = $participantRepository->findOneBy($criteria);

            // Vérification de l'existence de l'utilisateur
            if ($user){

                // Génération d'un jeton de réinitialisation de mot de passe
                $token = $tokenGenerator->generateToken();
                $user->setResetToken($token);
                $entityManager->persist($user);
                $entityManager->flush();

                // Génération d'un lien de réinitialisation de mot de passe contenant le jeton
                $url = $this->generateUrl('reset_pass',['token' => $token],
                UrlGeneratorInterface::ABSOLUTE_URL);

                // Création des données du mail
                $context=compact('url','user');

                //Envoi du mail
                $mail->send('453f7e6784-9562de@inbox.mailtrap.io',
                $user->getEmail(), 'Réinitialisation du mot de passe', 'password_reset', $context);
                $this->addFlash('success','E-mail envoyé');

                dd($url);

                // Redirection vers la page de connexion
                return $this->redirectToRoute('security_login');
            }

            // Message flash d'erreur
            $this->addFlash('danger', 'un problème est survenu');

            // Redirection vers la page de connexion
            return $this->redirectToRoute('security_login');
        }

        // Affichage du formulaire de demande de réinitialisation de mot de passe
        return $this->render('security/reset_password_request.html.twig',[
            'requestPassForm' => $form->createView()
        ]);
    }

    #[Route(path: '/forgetPassword/{token}', name: 'reset_pass', methods:['GET','POST'])]
    public function resetPass(string $token, Request $request, EntityManagerInterface $entityManager,UserPasswordHasherInterface $userPasswordHasher):Response
    {
        //on vérifie si on a le token enregistré
        $user = $entityManager->getRepository(Participant::class)->findOneBy(['resetToken' => $token]);

        if($user){

            // Création du formulaire de modification du mot de passe
            $form = $this->createForm(ResetPassWordFormType::class);

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                // On efface le token de réinitialisation
                $user->setResetToken('');

                // On met à jour le mot de passe de l'utilisateur
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );

                // On enregistre les modifications dans la base de données
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success','mot de passe modifier');
                return $this->redirectToRoute('security_login');
            }

            return $this->render('security/reset_password.html.twig',[
                'PassForm' => $form->createView()
            ]);
        }

        // Si le token est invalide ou inexistant, on affiche un message d'erreur
        $this->addFlash('danger','jeton invalide');
        return $this->redirectToRoute('security_login');
    }
}
