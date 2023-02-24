<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route(path: 'lieu/')]
class LieuController extends AbstractController
{

    #[Route('create', name: 'createLieu', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {

        // Création
        $lieu = new Lieu();


        //Création du formulaire
        $formLieu = $this->createForm(LieuFormType::class, $lieu);
        $formLieu->handleRequest($request);



        //Vérification du formulaire
        if ($formLieu->isSubmitted() && $formLieu->isValid()) {


            //Insertion du Participant en BDD (Base de donnée)
            $entityManager->persist($lieu);
            $entityManager->flush();

            $this->addFlash('success', 'Le souhait a bien été ajouté !');

            // Redirection vers la liste
            return $this->redirectToRoute('home_home');

        }

        return $this->render('lieu/createLieu.html.twig', [
            'lieuForm' => $formLieu->createView(),
        ]);
    }
}
