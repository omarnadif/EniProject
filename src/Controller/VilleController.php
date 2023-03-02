<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\VilleFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VilleController extends AbstractController
{
    #[Route(path: 'ville/index', name: 'indexville', methods:['GET'])]
    public function indexVille(EntityManagerInterface $em): Response
    {
        $villes = $em->getRepository(Ville::class)->findAll();

        return $this->render('ville/indexVille.html.twig', [
            'villes' => $villes,
        ]);
    }

    #[Route('ville/create', name: 'createVille', methods: ['GET', 'POST'])]
    public function createVille(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Création d'une Ville
        $ville = new Ville();

        //Création du formulaire
        $formVille = $this->createForm(VilleFormType::class, $ville);
        $formVille->handleRequest($request);

        //Vérification du formulaire
        if ($formVille->isSubmitted() && $formVille->isValid()) {

            //Insertion de la Ville en BDD (Base de donnée)
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

    #[Route(path: '/admin/ville/update/{id}', name: 'updateVille', methods: ['GET','POST'])]
    public function updateVille($id, EntityManagerInterface $em,Request $request): Response
    {
        $ville = $em->find(Ville::class, $id);

        //Création du formulaire
        $formVille = $this->createForm(VilleFormType::class, $ville);
        $formVille->handleRequest($request);

        //Vérification du formulaire
        if ($formVille->isSubmitted() && $formVille->isValid()) {

            //Insertion de la Ville en BDD (Base de donnée)
            $em->persist($ville);
            $em->flush();

            $this->addFlash('success', 'La ville a bien été modifiée !');

            // Redirection vers la liste
            return $this->redirectToRoute('indexville');
        }

        if ($ville === null) {
            // la ville n'a pas été trouvée
            return $this->render('ville/indexVille.html.twig', [
                'ville' => $ville,]);
        } else {
            // la ville a été trouvée
        }

        return $this->render('ville/updateVille.html.twig', [
            'ville' => $ville,
            'formVille' => $formVille->createView(),
        ]);
    }

    #[Route(path: '/admin/ville/delete/{id}', name: 'deleteVille', methods: ['GET'])]
    public function deleteVille($id, EntityManagerInterface $em): Response
    {
        $ville = $em->find(Ville::class, $id);

        if ($ville === null) {
            // la ville n'a pas été trouvée
        } else {
            $em->remove($ville);
            $em->flush();
            return $this->redirectToRoute('indexville');
        }

        return $this->render('ville/indexVille.html.twig', [
            'ville' => $ville,
        ]);
    }
}
