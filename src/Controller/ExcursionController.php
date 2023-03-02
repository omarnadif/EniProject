<?php

namespace App\Controller;

use App\Entity\Lieu;
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


    #[Route(path: 'updateExcursion', name: 'updateExcursion', methods: ['GET'])]
    public function updateExcursion(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('excursions/updateExcursion.html.twig');
    }

    #[Route('editExcursion', name: 'editExcursion', methods: ['GET', 'POST'])]
    public function excursionForm(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sortie = new Sortie();
        //Création du formulaire
        $form = $this->createForm(CreerSortieFormType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $organisateur = $this->getUser();
            $sortie->setParticipantOrganise($organisateur);
            $entityManager->persist($sortie);
            $entityManager->flush();
        }

        return $this->render('excursions/EditeExcursion.html.twig', [
            'excursionForm' => $form->createView(),
        ]);
    }
}