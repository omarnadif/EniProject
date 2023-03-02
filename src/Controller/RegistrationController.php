<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Site;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('admin/gestionUser/register', name: 'security_register', methods: ['GET', 'POST'])]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        // Création du Participant
        $user = new Participant();

        //Par défault mettre ROLE_USER
        $user->setRoles((array)'ROLE_USER');

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

        return $this->redirectToRoute('indexGestionUser');
        //Connexion automatique du Participant
        /*return $userAuthenticator->authenticateUser($user, $authenticator, $request);*/
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }


    /*
    #[Route('admin/gestionUser/importUser', name: 'security_importUser', methods: ['GET', 'POST'])]
    public function importUser(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(Participant::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['file']->getData();
            if ($file) {
                $file = fopen($file, 'r');
                $data = [];

                // Vérification de la première ligne
                $headers = fgetcsv($file);
                if ($headers !== null && $headers[0] == 'username') {
                    // La première ligne contient des en-têtes de colonne, on la supprime
                    unset($headers);
                } else {
                    // La première ligne ne contient pas d'en-têtes de colonne, on se replace au début du fichier
                    rewind($file);
                }

                while (($line = fgetcsv($file)) !== FALSE) {
                    array_push($data, $line[0]);
                }
                fclose($file);

                foreach ($data as $line) {
                    $splited_line = explode(";", $line);
                    $splited_line[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $splited_line[0]);
                    if ($splited_line[0] != 'username') {
                        $user = new Participant();
                        $user->setNom($splited_line[0]);
                        $user->setPrenom($splited_line[1]);
                        $user->setPseudo($splited_line[2]);
                        $user->setTelephone($splited_line[3]);
                        $user->setEmail($splited_line[4]);

                        $site = $em->getRepository(Site::class)->findOneByLastCampusName($splited_line[5]);
                        if (is_null($site)) {
                            $site = new Site();
                            $site->setNom($splited_line[5]);
                            $em->persist($site);
                        }
                        $user->setSite($site);

                        //Vérification que le password et confirm password sont identiques
                        $plainPassword = $form->get('plainPassword')->getData();
                        if ($plainPassword) {
                            $hashedPassword = $userPasswordHasher->hashPassword($user, $plainPassword);
                            $user->setPassword($hashedPassword);
                        }

                        $user->setActif(true);

                        $em->persist($user);
                    }
                }

                $em->flush();
                $this->addFlash('success', 'Import réussis!');

            } else {
                $this->addFlash('danger', 'Problème d\'importation');
            }
        }

        return $this->render('registration/importUser.html.twig', [
            'importUserForm' => $form->createView()
        ]);
    }
    */
}
