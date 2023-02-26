<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\RegistrationFormType;
use App\Security\UserAuthenticator;
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
    #[Route('/register', name: 'security_register', methods: ['GET', 'POST'])]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, UserAuthenticator $authenticator, UserAuthenticatorInterface $userAuthenticator): Response
    {
        // Création du Participant
        $user = new Participant();

        //Création du formulaire
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        $user->setAdministrateur(false);
        $user->setActif(false);

        //Vérification du formulaire
        if ($form->isSubmitted() && $form->isValid()) {

            //Récupération des données du formulaire
            $user = $form->getData();

            //Vérification que le password et confirm password sont identiques
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $userPasswordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            /*

            // Récupération du rôle sélectionné
            $selectedRole = $form->get('roles')->getData();

            // Enregistrement du rôle dans la propriété roles de l'utilisateur
            $user->setRoles([$selectedRole]);

            //Détermination de la valeur de la propriété "administrateur" en fonction du rôle sélectionné
            if ($selectedRole === 'ROLE_ADMIN') {
                $user->setAdministrateur(true);
            } else {
                $user->setAdministrateur(false);
            }

            */

            //Insertion du Participant en BDD (Base de donnée)
            $entityManager->persist($user);
            $entityManager->flush();

            //Connexion automatique du Participant
            return $userAuthenticator->authenticateUser($user, $authenticator, $request);

        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
