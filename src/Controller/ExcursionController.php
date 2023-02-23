<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\CreerSortieFormType;
use App\Security\UserAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

#[Route(path: 'excursion/')]
class ExcursionController extends AbstractController
{
    #[Route(path: '', name: 'selectExcursion', methods: ['GET'])]
    public function SelectExcursion(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('excursions/excursion.html.twig');
    }


    #[Route(path: 'update', name: 'updateExcursion', methods: ['GET'])]
    public function updateExcursion(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('excursions/updateExcursion.html.twig');
    }

    #[Route('edite', name: 'editeExcursion', methods: ['GET', 'POST'])]
    public function excursionForm(Request $request, EntityManagerInterface $entityManager): Response
    {

        // Création
        $sortie = new Sortie();


        //Création du formulaire
        $form = $this->createForm(CreerSortieFormType::class, $sortie);
        $form->handleRequest($request);


        //Vérification du formulaire
        if ($form->isSubmitted() && $form->isValid()) {


            //Insertion du Participant en BDD (Base de donnée)
            $entityManager->persist($sortie);
            $entityManager->flush();

            //Connexion automatique du Participant
//            return $userAuthenticator->authenticateUser($user, $authenticator, $request);

        }

        return $this->render('excursions/EditeExcursion.html.twig', [
            'excursionForm' => $form->createView(),
        ]);
    }
}
