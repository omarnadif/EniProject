<?php

namespace App\Controller;



use App\Entity\Ville;
use App\Form\VilleFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route(path: '/admin/ville/')]
class VilleController extends AbstractController
{
    #[Route(path: 'index', name: 'indexville', methods:['GET'])]
    public function profile(EntityManagerInterface $em): Response
    {
        $villes = $em->getRepository(Ville::class)->findAll();

        return $this->render('ville/index.html.twig', [
            'villes' => $villes,
        ]);
    }

    #[Route('create', name: 'createVille', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {

        // Création
        $ville = new Ville();


        //Création du formulaire
        $formVille = $this->createForm(VilleFormType::class, $ville);
        $formVille->handleRequest($request);




        //Vérification du formulaire
        if ($formVille->isSubmitted() && $formVille->isValid()) {


            //Insertion du Participant en BDD (Base de donnée)
            $entityManager->persist($ville);
            $entityManager->flush();

            $this->addFlash('success', 'Le souhait a bien été ajouté !');

            // Redirection vers la liste
            return $this->redirectToRoute('indexville');

        }

        return $this->render('ville/createVille.html.twig', [
            'formVille' => $formVille->createView(),
        ]);
    }



}
