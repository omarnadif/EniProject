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

        // Vérification de la checkbox "Actif"
        if ($form->get('actif')->getData()) {
            $user->setActif(true);
        } else {
            $user->setActif(false);
        }

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
