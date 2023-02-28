<?php

namespace App\Controller;

use App\Entity\Site;
use App\Form\SitesFormType;
use App\Form\VilleFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/site/')]
class SitesController extends AbstractController
{
    #[Route(path: 'index', name: 'indexSite', methods:['GET'])]
    public function profile(EntityManagerInterface $em): Response
    {
        $sites = $em->getRepository(Site::class)->findAll();

        return $this->render('site/indexSite.html.twig', [
            'sites' => $sites,
        ]);
    }

    #[Route('create', name: 'createSite', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {

        // Création
        $site = new Site();


        //Création du formulaire
        $formSite = $this->createForm(SitesFormType::class, $site);
        $formSite->handleRequest($request);




        //Vérification du formulaire
        if ($formSite->isSubmitted() && $formSite->isValid()) {


            //Insertion du Participant en BDD (Base de donnée)
            $entityManager->persist($site);
            $entityManager->flush();


            // Redirection vers la liste
            return $this->redirectToRoute('indexSite');

        }

        return $this->render('site/createSite.html.twig', [
            'formSite' => $formSite->createView(),
        ]);
    }
    #[Route(path: 'delete/{id}', name: 'deleteSite', methods: ['GET'])]
    public function delete($id, EntityManagerInterface $em): Response
    {
        $site = $em->find(Site::class, $id);

        if ($site === null) {
            // la ville n'a pas été trouvée
        } else {
            $em->remove($site);
            $em->flush();
            return $this->redirectToRoute('indexSite');
        }

        return $this->render('site/indexSite.html.twig', [
            'site' => $site,
        ]);
    }

    #[Route(path: 'update/{id}', name: 'updateSite', methods: ['GET','POST'])]
    public function update($id, EntityManagerInterface $em,Request $request): Response
    {
        $site = $em->find(Site::class, $id);



        //Création du formulaire
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
            // la ville n'a pas été trouvée
            return $this->render('site/indexSite.html.twig', [
                'site' => $site,]);
        } else {
            // la ville a été trouvée
        }

        return $this->render('site/updateSite.html.twig', [
            'site' => $site,
            'formSite' => $formSite->createView(),
        ]);

    }

}

