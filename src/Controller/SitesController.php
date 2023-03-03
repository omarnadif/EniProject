<?php

namespace App\Controller;

use App\Entity\Site;
use App\Form\SitesFormType;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/site/')]
class SitesController extends AbstractController
{
    #[Route('create', name: 'createSite', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Création d'une instance de l'entité Site
        $site = new Site();

        //Création du formulaire à partir de l'entité Site
        $formSite = $this->createForm(SitesFormType::class, $site);

        //Traitement du formulaire
        $formSite->handleRequest($request);

        //Vérification de la soumission du formulaire et de sa validation
        if ($formSite->isSubmitted() && $formSite->isValid()) {

            //Insertion du Participant en BDD (Base de donnée)
            $entityManager->persist($site);
            $entityManager->flush();

            // Redirection vers la liste
            return $this->redirectToRoute('indexSite');
        }

        //Affichage du formulaire
        return $this->render('site/createSite.html.twig', [
            'formSite' => $formSite->createView(),
        ]);
    }

    #[Route(path: 'delete/{id}', name: 'deleteSite', methods: ['GET'])]
    public function delete($id, EntityManagerInterface $em): Response
    {
        // Recherche du site à supprimer en utilisant l'EntityManager et l'identifiant fourni
        $site = $em->find(Site::class, $id);

        // Vérification si le site existe dans la base de données
        if ($site === null) {
            // Si le site n'a pas été trouvé, on peut afficher un message d'erreur
        } else {
            // Si le site existe, on peut le supprimer de la base de données
            $em->remove($site);
            $em->flush();

            // Redirection vers la liste des sites
            return $this->redirectToRoute('indexSite');
        }

        // Affichage de la liste des sites, même si aucun site n'a été supprimé
        return $this->render('site/indexSite.html.twig', [
            'site' => $site,
        ]);
    }

    #[Route(path: 'update/{id}', name: 'updateSite', methods: ['GET','POST'])]
    public function update($id, EntityManagerInterface $em,Request $request): Response
    {
        // Récupération du site à modifier à partir de son id
        $site = $em->find(Site::class, $id);

        // Création du formulaire avec les données du site récupéré
        $formSite = $this->createForm(SitesFormType::class, $site);
        $formSite->handleRequest($request);

        //Vérification du formulaire
        if ($formSite->isSubmitted() && $formSite->isValid()) {

            //Insertion du Participant en BDD (Base de donnée)
            $em->persist($site);
            $em->flush();

            // Redirection vers la liste
            return $this->redirectToRoute('indexSite');
        }

        if ($site === null) {
            // le site n'a pas été trouvé dans la base de données
            return $this->render('site/indexSite.html.twig', [
                'site' => $site,]);
        } else {
            // le site a été trouvé dans la base de données
        }

        // Affichage du formulaire de modification du site
        return $this->render('site/updateSite.html.twig', [
            'site' => $site,
            'formSite' => $formSite->createView(),
        ]);
    }

    #[Route(path: 'index', name: 'indexSite', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $em, SiteRepository $siteRepository): Response
    {
        // Récupération de la valeur de recherche envoyée via le formulaire
        $searchTerm = $request->request->get('searchTerm');

        if ($searchTerm) {
            // Recherche de tous les sites qui correspondent à la recherche effectuée
            $sites = $siteRepository->search($searchTerm);
        } else {
            // Récupération de tous les sites existants en BDD
            $sites = $siteRepository->findAll();
        }

        // Affichage de la vue contenant la liste des sites
        return $this->render('site/indexSite.html.twig', [
            'sites' => $sites,
        ]);
    }
}

