<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Form\LieuFormType;
use App\Form\VilleFormType;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route(path: 'admin/lieu/')]
class LieuController extends AbstractController
{
    #[Route(path: 'index/', name: 'indexlieu', methods:['GET'])]
    public function profile(EntityManagerInterface $em): Response
    {

        $villes = $em->getRepository(Ville::class)->findAll();

        return $this->render('lieu/indexLieu.html.twig', [
            'villes' => $villes,
        ]);
    }

    #[Route(path: 'search/ville/', name:'search_lieu', requirements: ['q' => '?q=*'], methods:['GET','POST'])]
    public function search(Request $request, VilleRepository $villeRepository): Response
    {
        $searchTerm = $request->query->get('q');

        $villes = $villeRepository->createQueryBuilder('l')
            ->andWhere('l.nom LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->getQuery()
            ->getResult();

        return $this->render('lieu/indexLieu.html.twig', [
            'villes' => $villes
        ]);

    }


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

    #[Route(path: 'update/{id}', name: 'updateVille_Lieu', methods: ['GET','POST'])]
    public function update($id, EntityManagerInterface $em,Request $request): Response
    {
        $ville = $em->find(Ville::class, $id);
        // Création



        //Création du formulaire
        $formVille = $this->createForm(VilleFormType::class, $ville);
        $formVille->handleRequest($request);




        //Vérification du formulaire
        if ($formVille->isSubmitted() && $formVille->isValid()) {


            //Insertion du Participant en BDD (Base de donnée)
            $em->persist($ville);
            $em->flush();

            $this->addFlash('success', 'La ville a bien été modifier !');

            // Redirection vers la liste
            return $this->redirectToRoute('indexlieu');

        }

        if ($ville === null) {
            // la ville n'a pas été trouvée
            return $this->render('lieu/indexLieu.html.twig', [
                'ville' => $ville,]);
        } else {
            // la ville a été trouvée
        }

        return $this->render('ville/updateVille.html.twig', [
            'ville' => $ville,
            'formVille' => $formVille->createView(),
        ]);

    }

    #[Route(path: 'delete/{id}', name: 'deleteVille_Lieu', methods: ['GET'])]
    public function delete($id, EntityManagerInterface $em): Response
    {
        $ville = $em->find(Ville::class, $id);

        if ($ville === null) {
            // la ville n'a pas été trouvée
        } else {
            $em->remove($ville);
            $em->flush();
            return $this->redirectToRoute('indexlieu');
        }

        return $this->render('lieu/indexLieu.html.twig', [
            'ville' => $ville,
        ]);
    }



}
